<?php
namespace Nodevo\MailBundle\Controller;

use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Recommandation controller.
 */
class RecommandationController extends Controller
{
    /**
     * Popin.
     */
    public function popinAction(Request $request)
    {
        $recommandationMail = $this->container->get('nodevo_mail.manager.mail')->findOneById(Mail::MAIL_RECOMMANDATION_AMI_ID);
        if (null === $recommandationMail) {
            throw new \Exception('Mail de recommandation à un ami inexistant.');
        }

        $recommandationForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $recommandationMail,
            'expediteur' => $this->getUser(),
            'url' => $request->headers->get('referer')
        ]);
        $recommandationForm->handleRequest($request);

        if ($recommandationForm->isSubmitted()) {
            $recommandationMessage = $this->container->get('nodevo_mail.manager.mail')->sendMail(
                $recommandationForm->get('objet')->getData(),
                $recommandationForm->get('expediteur')->getData(),
                $recommandationForm->get('destinataire')->getData(),
                $recommandationForm->get('message')->getData()
            );
            $this->container->get('mailer')->send($recommandationMessage);
            $this->addFlash('success', 'Recommandation envoyée.');
            return $this->redirect($recommandationForm->get('url')->getData());
        }

        return $this->render(
            'NodevoMailBundle:Recommandation:popin.html.twig',
            [
                'recommandationForm' => $recommandationForm->createView()
            ]
        );
    }
}
