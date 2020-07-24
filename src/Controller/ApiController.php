<?php

namespace App\Controller;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /**
     * @Route("/api/regions/api", name="api_all_regions", methods={"GET"})
     */
    public function addRegions(SerializerInterface $serializer)
    {
    //Recupere les Regions en Json
    $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
    
    //Methode 1:
    //$regionTab=$serializer->decode($regionJson,"json");
    //$serializer->denormalize($regionTab, 'App\Entity\Region') => Region(Tab) to Object
    //$serializer->denormalize($regionTab, 'App\Entity\Region[]') => Tab Region(tab) to Tab Object
    //$regionObject=$serializer->denormalize($regionTab, 'App\Entity\Region[]');
    $entityManager = $this->getDoctrine()->getManager();
    //Methode 2:
    $regionObject = $serializer->deserialize($regionJson, 'App\Entity\Region[]','json');
    
    foreach($regionObject as $region){
    $entityManager->persist($region);

    }
    $entityManager->flush();
    

    return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
    }

     /**
     * @Route("/api/regions", name="api_all_region",methods={"GET"})
     */
    public function showRegion(SerializerInterface $serializer,RegionRepository $repo)
    {
    $regionsObject=$repo->findAll();
    $regionsJson =$serializer->serialize($regionsObject,"json",
    [
    "groups"=>["region:read_all"]
    ]
    );
    return new JsonResponse($regionsJson,Response::HTTP_OK,[],true);
    }

    /**
     * @Route("/api/regions", name="api_add_region",methods={"POST"})
     */
    public function addRegion(Request $request,ValidatorInterface $validator,SerializerInterface $serializer)
    {
    $region = $serializer->deserialize($request->getContent(), Region::class,'json');
    $errors = $validator->validate($region);
    if (count($errors) > 0) {
    $errorsString =$serializer->serialize($errors,"json");
    return new JsonResponse( $errorsString ,Response::HTTP_BAD_REQUEST,[],true);
    }
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($region);
    $entityManager->flush();
    return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
    }
}
