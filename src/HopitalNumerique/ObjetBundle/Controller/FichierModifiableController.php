<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ObjetBundle\Entity\Objet;

class FichierModifiableController extends Controller
{
    /**
     * Formulaire d'edition d'une entité fichier modifiable liée à l'objet
     *
     * @return [type]
     */
    public function indexAction(Objet $objet)
    {
        $fichierModifiable = $objet->getFichierModifiable();

        if(is_null($fichierModifiable))
            $fichierModifiable = $this->get('hopitalnumerique_objet.manager.fichiermodifiable')->createEmpty();

        $objet->setFichierModifiable($fichierModifiable);

        //Création du formulaire via le service
        //$form = $this->createForm('hopitalnumerique_objet.form.fichiermodifier', $fichierModifiable);
        $form = $this->createForm(new \HopitalNumerique\ObjetBundle\Form\FichierModifiableType($this->get('hopitalnumerique_module.manager.fichiermodifiable') ), $fichierModifiable);
        
        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);
            
            //si le formulaire est valide
            if ($form->isValid())
            {
                $fichierModifiable->setObjet($objet);
                $this->get('hopitalnumerique_objet.manager.fichiermodifiable')->save($fichierModifiable);
                $this->get('session')->getFlashBag()->add( 'success', 'Informations enregistrées.' );
            }
            else
            {
                $this->get('session')->getFlashBag()->add( 'danger', 'Erreur dans l\'ajout du fichier.' );
            }

            $do = $request->request->get('do');
            return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_objet_objet_edit', array('id' => $objet->getId() ) ) : $this->generateUrl('hopitalnumerique_objet_administration_fichierModifiable', array( 'id' => $objet->getId() ) ) ) );
        }

        return $this->render( 'HopitalNumeriqueObjetBundle:FichierModifiable:edit.html.twig' , array(
            'formFichier'       => $form->createView(),
            'objet'             => $objet,
            'fichierModifiable' => $fichierModifiable
        ));
    }
}
