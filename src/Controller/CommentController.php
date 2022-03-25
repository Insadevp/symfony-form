<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostFormType;
use App\Form\CommentFormType;

use App\Repository\PostRepository;

class CommentController extends AbstractController
{
    /**
     * @Route("/post/{id}/comment/create", name="commentCreate")
     */
    public function create(int $id, ManagerRegistry $doctrine, Request $request): Response
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

        $comment = new Comment();
        $form = $this->createForm(
            CommentFormType::class,
            $comment,
            [
                'action' => $this->generateUrl('commentCreate', [
                    'id' => $post->getId()
                ]),
                'method' => 'GET',
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);
            $em = $doctrine->getManager();
            // Enregistre dans le cache de Doctrine
            $em->persist($comment);
            // INSERT ... IN post -- Enregistre en base de donnée
            $em->flush();
            //    Add comment to inform user of the post creation
            $this->addFlash(
                'info',
                'Saved new comment with id ' . $comment->getId()
            );
        }
        return $this->redirect($this->generateUrl('postShow', [
            'id' => $post->getId()/*ou 'id' => $id*/
        ]));
    }

    /**
     *   @Route("/post/{postId}/comment/edit/{commentId}", name="commentEdit")
     */

    public function edit(int $postId, int $commentId = null, ManagerRegistry $doctrine, Request $request): Response
    {
        $comment = $doctrine->getRepository(Comment::class)->find($commentId);

        if (!$commentId) {
            $this->addFlash(
                'warning',
                'There are no comment with the following id: ' . $postId
            );
            return $this->redirect($this->generateUrl('postsList'));
        }
        $form = $this->createForm(
            CommentFormType::class,
            $comment,
            [
                'action' => $this->generateUrl('commentCreate', [
                    'id' => $commentId
                ]),
                'method' => 'GET',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $em = $doctrine->getManager();
            // Enregistre dans le cache de Doctrine
            $em->persist($comment);
            // INSERT ... IN post -- Enregistre en base de donnée
            $em->flush();
            //    Add comment to inform user of the post creation
            $this->addFlash(
                'info',
                'The Comment have been edited'
            );
        }
        return $this->redirect($this->generateUrl('postShow', ['id' => $postId]));
    }

    /**
     * @Route("/post/{postId}/comment/delete/{commentId}" , name="commentDelete",)
     */
    public function delete(int $postId, int $commentId, ManagerRegistry $doctrine, Request $request)
    {

        $comment = $doctrine->getRepository(Comment::class)->find($commentId);
        $submittedToken = $request->request->get('_token');

        // 'delete-post' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-comment', $submittedToken)) {
            if (!$comment) {
                $this->addFlash(
                    'warning',
                    'There are no post with the following id: ' . $comment
                );
            } else {
                $em = $doctrine->getManager();
                $em->remove($comment);
                $em->flush();
                $this->addFlash(
                    'info',
                    'Comment with id ' . $commentId . ' deleted with success '
                );
            }
        } else {
            $this->addFlash(
                'warning',
                'The CSRF token is not valid !'
            );
        }
        return $this->redirect($this->generateUrl('postShow', ['id' => $postId]));
    }
}