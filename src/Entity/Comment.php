<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Post;





/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $author;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;


    /**
     * @ORM\ManyToOne(targetEntity="Post")
     */
    private $post;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    public function getPost(): Post
    {
        return $this->post;
    }
    public function setPost(Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function offsetExists($offset)
    {
        // In this example we say that exists means it is not null
        $value = $this->{"get$offset"}();
        return $value !== null;
    }

    public function offsetSet($offset, $value)
    {
        $this->{"set$offset"}($value);
    }

    public function offsetGet($offset)
    {
        return $this->{"get$offset"}();
    }

    public function offsetUnset($offset)
    {
        $this->{"set$offset"}(null);
    }
}