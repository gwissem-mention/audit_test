<?php

namespace Nodevo\MailBundle\Controller;

use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Event\RecommendationLoggerEvent;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use Nodevo\MailBundle\NodevoMailEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Recommandation controller.
 */
class RecommandationController extends Controller
{
    /**
     * Popin.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function popinAction(Request $request)
    {
        $recommandationMail = $this->get('nodevo_mail.manager.mail')->findOneById(Mail::MAIL_RECOMMANDATION_AMI_ID);

        if (null === $recommandationMail) {
            throw new \Exception('Mail de recommandation à un ami inexistant.');
        }

        $recommandationForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $recommandationMail,
            'expediteur' => $this->getUser(),
            'url' => null === $request->get('url') ? $request->headers->get('referer') : $request->get('url'),
        ]);

        $recommandationForm->handleRequest($request);

        if ($recommandationForm->isSubmitted()) {
            $recommandationMessage = $this->get('nodevo_mail.manager.mail')->sendMail(
                $recommandationForm->get('objet')->getData(),
                $recommandationForm->get('expediteur')->getData(),
                $recommandationForm->get('destinataire')->getData(),
                $recommandationForm->get('message')->getData()
            );
            $this->get('mailer')->send($recommandationMessage);
            $this->addFlash('success', 'Recommandation envoyée.');
            $this->get('event_dispatcher')->dispatch(
                NodevoMailEvents::RECOMMENDATION_SENDED,
                new RecommendationLoggerEvent($recommandationForm->get('destinataire')->getData(), $this->getUser())
            );

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render(
            'NodevoMailBundle:Recommandation:popin.html.twig',
            [
                'recommandationForm' => $recommandationForm->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function topicRecommandationAction(Request $request)
    {
        $recommandationMail = $this->get('nodevo_mail.manager.mail')->findOneById(Mail::MAIL_RECOMMANDATION_TOPIC_ID);

        if (null === $recommandationMail) {
            throw new \Exception('Mail de recommandation à un ami inexistant.');
        }

        $recommandationForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $recommandationMail,
            'expediteur' => $this->getUser(),
            'url' => $request->headers->get('referer'),
        ]);
        $recommandationForm->handleRequest($request);

        if ($recommandationForm->isSubmitted()) {
            $recommandationMessage = $this->get('nodevo_mail.manager.mail')->sendMail(
                $recommandationForm->get('objet')->getData(),
                $recommandationForm->get('expediteur')->getData(),
                $recommandationForm->get('destinataire')->getData(),
                $recommandationForm->get('message')->getData()
            );
            $this->get('mailer')->send($recommandationMessage);
            $this->addFlash('success', 'Recommandation envoyée.');
            $this->get('event_dispatcher')->dispatch(
                NodevoMailEvents::RECOMMENDATION_SENDED,
                new RecommendationLoggerEvent($recommandationForm->get('destinataire')->getData(), $this->getUser())
            );

            return $this->redirect($recommandationForm->get('url')->getData());
        }

        return $this->render(
            'NodevoMailBundle:Recommandation:popin.html.twig',
            [
                'recommandationForm' => $recommandationForm->createView(),
            ]
        );
    }
}
