<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {$user = $this->getUser();
        if($user){
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);}else 
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request, UploadableManager $uploadableManager): Response
    {
        if($this->getUser()){
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);

            if($form->get('cover_file')->getData()) {
                $uploadableManager->markEntityToUpload($post, $form->get('cover_file')->getData());
            }

            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);}
        else
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        if($this->getUser()){
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);}
        else
        return $this->redirectToRoute('app_login');

    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post, UploadableManager $uploadableManager): Response
    {
        if($this->getUser()){
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form);
            if($form->get('cover_file')->getData()) {
                $uploadableManager->markEntityToUpload($post, $form->get('cover_file')->getData());
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);}
        else
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if($this->getUser()){
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
    else
    return $this->redirectToRoute('app_login');
    }
}
