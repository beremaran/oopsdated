<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 06.03.2019
 * Time: 16:37
 */

namespace App\Controller;


use App\Form\SubscriptionType;
use App\Repository\GithubRepoRepository;
use Enqueue\Client\ProducerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param GithubRepoRepository $repoRepository
     * @param ProducerInterface $producer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request,
                          GithubRepoRepository $repoRepository,
                          ProducerInterface $producer)
    {

        $form = $this->createForm(SubscriptionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $producer->sendEvent('createSubscription', json_encode($data));
            return $this->redirectToRoute('subscribe.success');
        }

        return $this->render('landing/landing.html.twig', [
            'subscriptionForm' => $form->createView(),
            'popularRepositories' => $repoRepository->getPopularRepositories(5)
        ]);
    }

    /**
     * @Route("/subscribed", name="subscribe.success")
     */
    public function subscriptionCompleted()
    {
        return $this->render('subscription/success.html.twig');
    }

    /**
     * @Route("/{repoName}", name="inspect", requirements={"repoName"=".+"})
     * @param $repoName
     * @param GithubRepoRepository $repoRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function inspect($repoName, GithubRepoRepository $repoRepository)
    {
        $repo = $repoRepository->findOneByName($repoName);
        if ($repo === null) {
            return $this->render('inspect/not_found.html.twig');
        }

        $report = $repo->getScanResult();
        if ($report === null) {
            return $this->render('inspect/not_scanned.html.twig', [
                'repoName' => $repo->getName()
            ]);
        }

        $report = $report->getJsonReport();

        return $this->render('inspect/repository.html.twig', [
            'nOutdated' => $report['nOutdated'],
            'repoName' => $repo->getName(),
            'packageManagers' => $report['report']
        ]);
    }
}