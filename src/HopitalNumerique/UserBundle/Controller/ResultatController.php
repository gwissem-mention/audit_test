<?php
namespace HopitalNumerique\UserBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller des Résultats par utilisateurs
 */
class ResultatController extends Controller
{
    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(User $user)
    {
        $syntheses = $this->get('autodiag.repository.synthesis')->findBy(['user' => $user->getId()]);
        $shareNamesBySynthesis = [];

        /** @var Synthesis $synthesis */
        foreach ($syntheses as $synthesis) {
            /** @var User $user */
            foreach ($synthesis->getShares() as $user) {
                if (!isset($shareNamesBySynthesis[$synthesis->getId()])) {
                    $shareNamesBySynthesis[$synthesis->getId()] = [];
                }

                $shareNamesBySynthesis[$synthesis->getId()][] = $user->getNomPrenom();
            }
        }

        return $this->render('HopitalNumeriqueUserBundle:Resultat:index.html.twig', [
            'user'                  => $user,
            'options'               => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'results'               => $syntheses,
            'shareNamesBySynthesis' => $shareNamesBySynthesis,
        ]);
    }
}