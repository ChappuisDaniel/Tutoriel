<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;

use App\Form\CommentType;
use App\Repository\ArticleRepository;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * Affiche la page d'accueil
     * 
     * @Route("/", name="home")
     */
    public function home() {
        return $this->render('blog/home.html.twig');
    }

    /**
     * Affiche la liste des articles
     * 
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    /**
     * Ajoute ou modifie un article
     * 
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $manager) {
        // Nouvel article
        if(!$article) {
            $article = new Article();
            $article->setCreatedAt(new \DateTime);
        }

        $form = $this->createForm(ArticleType::class, $article);

        // Entregistrement de l'article
        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($article);
            $manager->flush();

            // Redirection sur la page de lecture
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        // Affichage page édition
        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }
    
    /**
     * Affiche l'article depuis le modèle
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, ObjectManager $manager) {
        $comment = new Comment();
        
        $form = $this->createForm(CommentType::class, $comment);

        // Entregistrement du commentaire
        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime);
            $comment->setArticle($article);

            $manager->persist($comment);
            $manager->flush();

            // Redirection sur la page de lecture
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

            return $this->render('blog/show.html.twig', [
            'article' => $article,
            'formComment' => $form->createView(),
        ]);
    }

}
