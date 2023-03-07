<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    #[Route('/list', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        $filterForm = $this->createForm(FiltreType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            $filters = $filterForm->getData();

            $sorties =$sortieRepository-> findFiltered($filters);
        } else {
            //$sorties = $sortieRepository->findAllOrderedBySites();
            $sorties = $sortieRepository->findByNom('');
        }


        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/sortie/{id}', name: 'details')]
    public function sortie(SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Oups, cette sortie n\'existe pas');
        }

        return $this->render('sortie/sortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, UserRepository $userRepository): Response
    {

        $sortie = new Sortie();
        $ville = new Ville();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // Récupérer la ville sélectionnée dans le formulaire
            $ville = $sortieForm->get('ville')->getData();

            // Définir la ville de la sortie
            $sortie->setVille($ville);

            // Enregistrer la sortie en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sortie);
            $entityManager->flush();

            // Rediriger l'utilisateur vers la page de détails de la sortie
            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

//            if ($sortieForm->get('enregistrer')) {
//                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
//            } elseif ($sortieForm->get('publier')) {
//                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Publiée']));
//            }
//            if ($sortieForm->get('inscriptionAuto')) {
//                $sortie->setOrganisateur($participantRepository->find($this->getUser()->getId()));
//            }
//
//            $sortieRepository->add($sortie, true);
//
//            if ($sortieForm->get('enregistrer')) {
//                $this->addFlash('success', 'Sortie créée');
//            } elseif ($sortieForm->get('publier')) {
//                $this->addFlash('success', 'Sortie publiée');
//            }
//            return $this->redirectToRoute('sortie_sortie', [
//                'id' => $sortie->getId()
//            ]);



        return $this->render('sortie/new.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}
