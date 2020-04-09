<?php

namespace App\Controller;
use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Form\AjoutArticleFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ArticlesController
 * @package App\Controller
 * @Route("/actualites",name="actualites_")
 */

class ArticlesController extends AbstractController
{
    /**
    * @Route("/", name="articles")
    */
    public function index()
    {
    // Méthode findBy qui permet de récupérer les données avec des critères de filtre et de tri
    $articles = $this->getDoctrine()->getRepository(Article::class)->findBy([],['created_at' => 'desc']);
    return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }
        /**
        *@IsGranted("ROLE_MODO") 
        * @Route("/nouveau", name="ajoutarticle")
        */
    public function ajoutArticle(Request $request)
    {
         // Nous créons l'instance de "article"
        $article = new Article();
        // Nous créons le formulaire en utilisant "CommentairesType" et on lui passe l'instance
        $form = $this->createForm(AjoutArticleFormType::class, $article);

        // Nous récupérons les données
        $form->handleRequest($request);
        // Nous vérifions si le formulaire a été soumis et si les données sont valides
            if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($this->getUser());
        // Hydrate notre commentaire avec la date et l'heure courants
            $article->setCreatedAt(new \DateTime('now'));
            $doctrine = $this->getDoctrine()->getManager();
        // On hydrate notre instance $commentaire
            $doctrine->persist($article);
        // On écrit en base de données
            $doctrine->flush();
            }

            return $this->render('articles/ajout.html.twig', [
                'articleForm' => $form->createView()
            ]);
            }

    /**
    * @Route("/{slug}", name="article")
    */
    public function article($slug, Request $request)
    {
        // On récupère l'article correspondant au slug
        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        // On récupère les commentaires actifs de l'article
        $commentaires = $this->getDoctrine()->getRepository(Commentaire::class)->findBy([
            'article' => $article,
            'actif' => 1
        ],['created_at' => 'desc']);
        
    
        if(!$article)
        {
        // Si aucun article n'est trouvé, nous créons une exception
        throw $this->createNotFoundException('L\'article n\'existe pas');
        }
        // Nous créons l'instance de "Commentaires"
        $commentaire = new Commentaire();  
        // Nous créons le formulaire en utilisant "CommentairesType" et on lui passe l'instance
        $form = $this->createForm(CommentaireType::class, $commentaire); 
        
       
        // Nous récupérons les données
            $form->handleRequest($request);
        // Nous vérifions si le formulaire a été soumis et si les données sont valides
            if ($form->isSubmitted() && $form->isValid()) {
        // Hydrate notre commentaire avec l'article
            $commentaire->setArticle($article);
        // Hydrate notre commentaire avec la date et l'heure courants
            $commentaire->setCreatedAt(new \DateTime('now'));
            $doctrine = $this->getDoctrine()->getManager();
        // On hydrate notre instance $commentaire
            $doctrine->persist($commentaire);
        // On écrit en base de données
            $doctrine->flush();
        }
         // Si l'article existe nous envoyons les données à la vue
         return $this->render('articles/article.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'commentaire' => $commentaires,
            ]);
         }
        /**
         * @Route("/article/modifier/{id}", name="modif_article")
         */
        public function modifArticle(){
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            // Ici il faut être admin
        }
    }
