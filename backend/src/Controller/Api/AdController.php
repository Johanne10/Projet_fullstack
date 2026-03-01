<?php

namespace App\Controller\Api;

use App\Entity\Ad;
use App\Repository\AdRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AdController extends AbstractController
{
    #[Route('/api/ads', name: 'api_ads_list', methods: ['GET'])]
    public function list(AdRepository $adRepository): JsonResponse
    {
        return $this->json($adRepository->findAll(), 200, [], [
            'groups' => ['ad:read'],
        ]);
    }

    #[Route('/api/ads/{id}', name: 'api_ads_show', methods: ['GET'])]
    public function show(Ad $ad): JsonResponse
    {
        return $this->json($ad, 200, [], [
            'groups' => ['ad:read'],
        ]);
    }

    #[Route('/api/ads', name: 'api_ads_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        CategoryRepository $categoryRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $required = ['title', 'description', 'price', 'city', 'authorId', 'categoryId'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return $this->json(['message' => "Champ manquant : $field"], 422);
            }
        }

        $author = $userRepo->find((int)$data['authorId']);
        if (!$author) {
            return $this->json(['message' => 'Auteur introuvable'], 404);
        }

        $category = $categoryRepo->find((int)$data['categoryId']);
        if (!$category) {
            return $this->json(['message' => 'Catégorie introuvable'], 404);
        }

        $ad = new Ad();
        $ad->setTitle((string)$data['title']);
        $ad->setDescription((string)$data['description']);
        $ad->setPrice((string)$data['price']);
        $ad->setCity((string)$data['city']);
        $ad->setAuthor($author);
        $ad->setCategory($category);
        $ad->setIsPublished((bool)($data['isPublished'] ?? true));

        $em->persist($ad);
        $em->flush();

        return $this->json($ad, 201, [], [
            'groups' => ['ad:read'],
        ]);
    }

    #[Route('/api/ads/{id}', name: 'api_ads_update', methods: ['PUT', 'PATCH'])]
    public function update(
        Ad $ad,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        CategoryRepository $categoryRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (array_key_exists('title', $data)) {
            $title = trim((string)$data['title']);
            if ($title === '') return $this->json(['message' => 'title ne peut pas être vide'], 422);
            $ad->setTitle($title);
        }

        if (array_key_exists('description', $data)) {
            $description = trim((string)$data['description']);
            if ($description === '') return $this->json(['message' => 'description ne peut pas être vide'], 422);
            $ad->setDescription($description);
        }

        if (array_key_exists('price', $data)) {
            $price = (string)$data['price'];
            if ($price === '') return $this->json(['message' => 'price ne peut pas être vide'], 422);
            $ad->setPrice($price);
        }

        if (array_key_exists('city', $data)) {
            $city = trim((string)$data['city']);
            if ($city === '') return $this->json(['message' => 'city ne peut pas être vide'], 422);
            $ad->setCity($city);
        }

        if (array_key_exists('isPublished', $data)) {
            $ad->setIsPublished((bool)$data['isPublished']);
        }

        if (array_key_exists('authorId', $data)) {
            $author = $userRepo->find((int)$data['authorId']);
            if (!$author) return $this->json(['message' => 'Auteur introuvable'], 404);
            $ad->setAuthor($author);
        }

        if (array_key_exists('categoryId', $data)) {
            $category = $categoryRepo->find((int)$data['categoryId']);
            if (!$category) return $this->json(['message' => 'Catégorie introuvable'], 404);
            $ad->setCategory($category);
        }

        $em->flush();

        return $this->json($ad, 200, [], [
            'groups' => ['ad:read'],
        ]);
    }

    #[Route('/api/ads/{id}', name: 'api_ads_delete', methods: ['DELETE'])]
    public function delete(Ad $ad, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($ad);
        $em->flush();

        return $this->json(null, 204);
    }
}