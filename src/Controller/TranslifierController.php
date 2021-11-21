<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Version;
use App\Entity\Language;
use Elasticsearch\Client;
use App\Form\LanguageType;
use Sepia\PoParser\Parser;
use App\Entity\Translation;
use Elasticsearch\ClientBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Sepia\PoParser\SourceHandler\FileSystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Nzo\FileDownloaderBundle\FileDownloader\FileDownloader;

use Sepia\PoParser\PoCompiler;

class TranslifierController extends AbstractController
{


    #[Route('/translations/{id}/create', name: 'app_translation_create')]
    public function create(Version $version, Request $request, EntityManagerInterface $em): Response
    {
        $message = new Message;
        $poFileName = $version->getProject()->getPoFilename();
        $path = "uploads/files/{$poFileName}";
        $fileHandler = new FileSystem($path);
        $poParser = new Parser($fileHandler);
        $catalog  = $poParser->parse();
        $entries = $catalog->getEntries();
        foreach ($entries as $entry) {
            $msgId[] = $entry->getMsgId();
            $msgStr[] = $entry->getMsgStr();
        }
        $message->setValue($msgId);
        $language = new Language;
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($language);
            $version->setLanguage($language);
            $em->persist($version);
            $em->persist($message);
            $translation = new Translation;
            $translation->setVersion($version);
            $translation->setMessage($message);
            $em->persist($translation);
            $em->flush();
            $this->addFlash('success', 'Traduction successfully created!');
            return $this->redirectToRoute('app_translation_show', array('id' => $translation->getId()));
        }
        return $this->render('translators/create.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/translations/{id}', name: 'app_translation_show')]
    public function show(Translation $translation): Response
    {
        return $this->render('translators/show.html.twig', compact('translation'));
    }
    #[Route('/translations/{id}/edit', name: 'app_translation_edit')]
    public function edit(Translation $translation, Request $request, EntityManagerInterface $em): Response
    {
        $language = $translation->getVersion()->getLanguage();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Traduction successfully updated!');
            return $this->redirectToRoute('app_version_show', array('id' => $translation->getVersion()->getId()));
        }
        return $this->render('translators/edit.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/translations/{id}/manual', name: 'app_translation_manual')]
    public function choices(Translation $translation, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add(
                'traduction',
                TextareaType::class,
                [
                    'attr' => ['cols' => 124],
                    'attr' => ['rows' => 8],
                ]
            )
            ->getForm();
        $form->handleRequest($request);
        $poFileName = $translation->getVersion()->getProject()->getPoFilename();
        $msgId = $translation->getMessage()->getValue();
        $poFileNameToModify = substr($poFileName, 0, -3) . 'toUpdate' . '.' . 'po';
        $path = "uploads/poFilesToModify/{$poFileNameToModify}";
        $fileHandler = new FileSystem($path);
        $poParser = new Parser($fileHandler);
        $catalog  = $poParser->parse();
        $entries = $catalog->getEntries();
        $entryNumber = count($entries);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('traduction')->getData();
            $newMsgStr = explode("\n", $data);
            for ($i = 0; $i < $entryNumber; $i++) {
                $entry = $catalog->getEntry($msgId[$i]);
                $entry->setMsgStr(substr($newMsgStr[$i], 0, -1));
            }
            $compiler = new PoCompiler();
            $fileHandler->save($compiler->compile($catalog));
            $this->addFlash('success', 'Your traduction has passed successfully');
            return $this->redirectToRoute('app_translation_download', array('id' => $translation->getId()));
        }
        return $this->render('translators/ManualMode.html.twig', ['translation' => $translation, 'form' => $form->createView()]);
    }
    #[Route('/translations/{id}/download', name: 'app_translation_download')]
    public function downloadFileFromPublicFolder(FileDownloader $fileDownloader, Translation $translation)
    {
        $poFileName = $translation->getVersion()->getProject()->getPoFilename();
        $language = $translation->getVersion()->getLanguage()->getName();
        $poFileNameToModify = substr($poFileName, 0, -3) . 'toUpdate' . '.' . 'po';
        $path = "uploads/poFilesToModify/{$poFileNameToModify}";
        # change the name of the file when downloading:
        $newFileName = substr($poFileName, 0, -16) . "{$language}" . '.' . 'po';
        return $fileDownloader->downloadFile($path, $newFileName);
    }










    // public function elasticSearchAdd(){
    //     $client = ClientBuilder::create()
    //         ->setHosts(['localhost:9200'])
    //         ->build();
    //     for ($i = 0; $i < count($msgId) - 1; $i++) {
    //         $params['body'][] = [
    //             'index' => [
    //                 '_index' => 'messages',
    //                 '_type' => 'message',
    //                 '_id'    => $i
    //             ]
    //         ];
    //         $params['body'][] = [
    //             'msgId'     => $msgId[$i],
    //             'msgStr' => $msgStr[$i]
    //         ];
    //     }

    //     $client->bulk($params); 
    // }



}
