<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitsType;
use App\Repository\CategoriesRepository;
use App\Repository\ProduitsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;




#[Route('/produits')]
class ProduitsController extends AbstractController
{
    #[Route('/', name: 'app_produits_index', methods: ['GET'])]
    public function index(ProduitsRepository $produitsRepository): Response
    {
        return $this->render('produits/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
        ]);
    }

    #[Route('/search', name: 'app_produit_search', methods: ['POST'])]
    public function searchProduit(ProduitsRepository $ProduitsRepository,PaginatorInterface $paginator, Request $request, SerializerInterface $serializerInterface): Response
{

    $data = $request->get('search');
    $produits = $ProduitsRepository->searchQB($data);

    $produits = $paginator->paginate(
        $produits, /* query NOT result */
        $request->query->getInt('page', 1)/*page number*/,
        3/*limit per page*/
    );


    $jsonData = json_decode(
        $serializerInterface->serialize(
            $produits,
            'json',
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['Categorie'],
                AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 1
            ]
        ),
        JSON_OBJECT_AS_ARRAY
    );
    return $this->json(['data' => $jsonData, 'html' => $this->renderView('produits/_produits_items.html.twig', ['produits' => $produits])]);
}    

    #[Route('/edit/{id}', name: 'app_produits_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produits $produit, ProduitsRepository $produitsRepository): Response
    {
        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadFile=$form['Image']->getData();
            $filename=md5(uniqid()).'.'.$uploadFile->guessExtension();//cryptage d image


            $uploadFile->move($this->getParameter('kernel.project_dir').'/public/uploads/produit_image',$filename);
            $produit->setImage($filename);

            $produitsRepository->save($produit, true);

            return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produits/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produits_delete', methods: ['POST'])]
    public function delete(Request $request, Produits $produit, ProduitsRepository $produitsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitsRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
    }
    
  

    #[Route('/showAll/front', name: 'app_produits_indexFront', methods: ['GET'])]
    public function indexFront(Request $request, ProduitsRepository $produitsRepository,CategoriesRepository $categoriesRepository, PaginatorInterface $paginator): Response
    {   
        $produits = $produitsRepository->findAll();

        $produits = $paginator->paginate(
            $produits, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );
        return $this->render('produits/indexFront.html.twig', [
            'produits' => $produits,
            'categories' => $categoriesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produits_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitsRepository $produitsRepository, SluggerInterface $slugger): Response
    {
        $produit = new Produits();
        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadFile=$form['Image']->getData();
            $filename=md5(uniqid()).'.'.$uploadFile->guessExtension();//cryptage d image


            $uploadFile->move($this->getParameter('kernel.project_dir').'/public/uploads/produit_image',$filename);
            $produit->setImage($filename);
            $produitsRepository->save($produit, true);

            return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produits/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produits_show', methods: ['GET'])]
    public function show(Produits $produit): Response
    {
        return $this->render('produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    

}
