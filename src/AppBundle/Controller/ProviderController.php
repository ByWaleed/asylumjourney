<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Provider;
use Nocarrier\Hal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class ProviderController extends Controller
{

    /**
     * @Route("/providers", name="list_providers", methods={"GET", "HEAD"})
     */
    public function listProvidersAction()
    {
        $normalizer = $this->normalizer();
        $providers = $this->getDoctrine()->getRepository("AppBundle:Provider")->findAll();

        $hal = new Hal('/providers', ['total' => count($providers)]);

        foreach ($providers as $provider) {
            $hal->addResource(
                'providers',
                (new Hal('/providers/' . $provider->getId()))->setData($normalizer->normalize($provider))
            );
        }

        return $this->halResponse($hal);
    }

    /**
     * @Route("/providers/{id}", name="read_provider", methods={"GET", "HEAD"})
     */
    public function readProviderAction($id)
    {
        $provider = $this->fetchProvider($id);

        if (!$provider) {
            return $this->notFoundResponse($id);
        }

        return $this->halResponse(
            (new Hal('/providers/' . $provider->getId()))
                ->setData($this->normalizer()->normalize($provider))
        );
    }

    /**
     * @Route("/providers", name="create_provider", methods={"POST"})
     */
    public function createProviderAction(Request $request)
    {
        $parametersAsArray = $this->parametersFromJson($request);

        $name = $parametersAsArray['name']; //return error
        $description = isset ($parametersAsArray['description']) ? $parametersAsArray['description'] : null;
        $phoneNumber = isset ($parametersAsArray['description']) ? $parametersAsArray['phoneNumber'] : null;
        $email = isset ($parametersAsArray['email']) ? $parametersAsArray['email'] : null;
        $website = isset ($parametersAsArray['website']) ? $parametersAsArray['website'] : null;
        $contactName = isset ($parametersAsArray['contactName']) ? $parametersAsArray['contactName'] : null;
        $address = isset ($parametersAsArray['addresss']) ? $parametersAsArray['addresss'] : null;
        $postcode = isset ($parametersAsArray['postcode']) ? $parametersAsArray['postcode'] : null;

        $provider = new Provider(
            $name, $description, $phoneNumber, $email, $website, $contactName, $address, $postcode
        );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($provider);
        $entityManager->flush();

        return new Response(
            null,
            Response::HTTP_CREATED,
            ['location' => '/providers/' . $provider->getId(), 'Content-Type' => 'application/json']
        );
    }

    /**
     * @Route("/providers/{id}", name="edit_provider", methods={"PUT"})
     */
    public function editProviderAction(Request $request, $id)
    {
        $provider = $this->fetchProvider($id);

        if (!$provider) {
            return $this->notFoundResponse($id);
        }

        $parametersAsArray = $this->parametersFromJson($request);

        $name = $parametersAsArray['name']; //return error
        $description = isset ($parametersAsArray['description']) ? $parametersAsArray['description'] : null;
        $phoneNumber = isset ($parametersAsArray['description']) ? $parametersAsArray['phoneNumber'] : null;
        $email = isset ($parametersAsArray['email']) ? $parametersAsArray['email'] : null;
        $website = isset ($parametersAsArray['website']) ? $parametersAsArray['website'] : null;
        $contactName = isset ($parametersAsArray['contactName']) ? $parametersAsArray['contactName'] : null;
        $address = isset ($parametersAsArray['addresss']) ? $parametersAsArray['addresss'] : null;
        $postcode = isset ($parametersAsArray['postcode']) ? $parametersAsArray['postcode'] : null;

        $provider->setName($name);
        $provider->setDescription($description);
        $provider->setPhone($phoneNumber);
        $provider->setEmail($email);
        $provider->setWebsite($website);
        $provider->setContactName($contactName);
        $provider->setAddress($address);
        $provider->setPostcode($postcode);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();;

        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/providers/{id}", name="delete_provider", methods={"DELETE"})
     */
    public function deleteProviderAction($id)
    {
        $provider = $this->fetchProvider($id);

        if (!$provider) {
            return $this->notFoundResponse($id);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($provider);
        $entityManager->flush();

        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function normalizer()
    {
        return (new GetSetMethodNormalizer());
    }

    private function halResponse(Hal $resource)
    {
        return new Response($resource->asJson(true), 200, ['Content-Type' => 'application/hal+json']);
    }

    /**
     * @param $id
     * @return Provider
     */
    private function fetchProvider($id)
    {
        return $this->getDoctrine()->getRepository("AppBundle:Provider")->find($id);
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    private function parametersFromJson(Request $request)
    {
        if ($content = $request->getContent()) {
            return json_decode($content, true);
        }
        return [];
    }

    /**
     * @param $id
     * @return Response
     */
    private function notFoundResponse($id)
    {
        return new Response(
            (new Hal(null, ['message' => 'Provider not found']))->addLink(
                'about',
                '/providers/' . $id
            )->asJson(true), 404, ['Content-Type' => 'application/vnd.error+json']
        );
    }
}
