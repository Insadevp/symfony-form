<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Entity\Comment;
use App\Repository\CommentRepository;



use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PostController extends AbstractController
{



    /**
     * @Route("/posts", name="postsList")
     */
    public function index(PostRepository $postRepo): Response
    {
        $posts = $postRepo->findAll();

        return $this->render('post/post-list.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $posts



        ]);
    }

    /**
     * @Route("/post/show/{id}", name="postShow")
     */
    public function show(int $id, ManagerRegistry $doctrine, Request $request, Post $post, CommentRepository $commentRepository): Response
    {
        $post = $doctrine->getRepository(Post::class);
        $post = $post->find($id);

        if (!$post) {
            $this->addFlash(
                'warning',
                'There are no post with the following id: ' . $id
            );
            return $this->redirect($this->generateUrl('postsList'));
        }
        // COMMENT FORM BUILDER AND CONFIGURE  and CONFIGURE THE ACTION ROUTE


        $commentRepo = $doctrine->getRepository(Comment::class);
        $comments = $commentRepo->findBy(['post' => $id]);
        $comment = new Comment();
        $form = $this->createForm(
            CommentFormType::class,
            $comment,
            [
                'action' => $this->generateUrl('commentCreate', [
                    'id' => $post->getId()
                ]),
                'method' => 'POST',
            ]
        );
        $form->handleRequest($request);

        return $this->render('post/post-show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/post/create", name="postCreate")
     */
    public function create(ManagerRegistry $doctrine, Request $request): Response
    {
        $post = new Post();

        $form = $this->createForm(PostFormType::class, $post);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $em = $doctrine->getManager();
            // Enregistre dans le cache de Doctrine
            $em->persist($post);
            // INSERT ... IN post -- Enregistre en base de donnée
            $em->flush();
            //    Add comment to inform user of the post creation
            $this->addFlash(
                'info',
                'Saved new post with id ' . $post->getId()
            );

            return $this->redirect($this->generateUrl('postEdit', ['id' => $post->getId()]));
        }

        return $this->render('post/post-edit.html.twig', [
            'form' => $form->createView(),


        ]);
    }

    /**
     *   @Route("/post/edit/{id}", name="postEdit")
     */
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        if (!$post) {
            $this->addFlash(
                'warning',
                'There are no post with the following id: ' . $id
            );
            return $this->redirect($this->generateUrl('postsList'));
        }

        /* $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('author', TextType::class)
            ->add('content', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Valider'])
            ->getForm(); */

        $form = $this->createForm(PostFormType::class, $post);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $em = $doctrine->getManager();
            // Enregistre dans le cache de Doctrine
            $em->persist($post);
            // INSERT ... IN post -- Enregistre en base de donnée
            $em->flush();

            $this->addFlash(
                'info',
                'Post updated with success '
            );
            return $this->redirect($this->generateUrl('postShow', ['id' => $id]));
        }

        return $this->render('post/post-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}" , name="postDelete")
     */
    public function deleteAction(int $id, ManagerRegistry $doctrine, Request $request): Response
    {

        $post = $doctrine->getRepository(Post::class)->find($id);
        $submittedToken = $request->request->get('token');

        // 'delete-post' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-post', $submittedToken)) {
            $em = $doctrine->getManager();
            if (!$post) {
                $this->addFlash(
                    'warning',
                    'There are no post with the following id: ' . $id
                );
            } else {
                $comments = $doctrine->getRepository(Comment::class);
                $comments = $comments->findBy(['post' => $id]);

                foreach ($comments as $com) {
                    $em->remove($com);
                }


                $em->remove($post);
                $em->flush();
                $this->addFlash(
                    'info',
                    'Post with id ' . $id . ' deleted with success '
                );
            }
        } else {
            $this->addFlash(
                'warning',
                'The CSRF token is not valid !'
            );
        }
        return $this->redirect($this->generateUrl('postsList'));
    }
}