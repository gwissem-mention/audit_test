<?php

namespace Nodevo\MenuBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nodevo\MenuBundle\Entity\Item;

class LoadItemData implements FixtureInterface
{
	/**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
		$item = new Item();
		$item->setName("Accueil");
		$item->setRoute("hopital_numerique_admin_homepage");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("fa fa-home");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des menus");
		$item->setRoute("nodevo_menu_menu");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("fa fa-sitemap");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion de l’annuaire");
		$item->setRoute("");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("javascript:;");
		$item->setIcon("fa fa-book");
		$item->setDisplay(true);
		$item->setDisplayChildren(true);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des utilisateurs");
		$item->setRoute("hopital_numerique_user_homepage");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des groupes");
		$item->setRoute("nodevo_role_role");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des habilitations");
		$item->setRoute("nodevo_acl");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Référentiel mail");
		$item->setRoute("nodevo_mail_mail");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des référentiels");
		$item->setRoute("");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("javascript:;");
		$item->setIcon("fa fa-list-alt");
		$item->setDisplay(true);
		$item->setDisplayChildren(true);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(4);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Référentiel générique");
		$item->setRoute("hopitalnumerique_reference_reference");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Référentiel établissement");
		$item->setRoute("hopitalnumerique_etablissement");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("javascript:;");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un lien de menu");
		$item->setRoute("nodevo_menu_item_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Liste des liens du menu");
		$item->setRoute("nodevo_menu_item");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer menu");
		$item->setRoute("nodevo_menu_menu_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Add menu");
		$item->setRoute("nodevo_menu_menu_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(4);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer lien de menu");
		$item->setRoute("nodevo_menu_item_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(5);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un groupe");
		$item->setRoute("nodevo_role_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Fiche Role");
		$item->setRoute("nodevo_role_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer un groupe");
		$item->setRoute("nodevo_role_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un utilisateur");
		$item->setRoute("hopital_numerique_user_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(5);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Afficher l'utilisateur");
		$item->setRoute("hopital_numerique_user_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(6);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer un utilisateur");
		$item->setRoute("hopital_numerique_user_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(7);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Voir arborescence");
		$item->setRoute("hopitalnumerique_reference_sitemap");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter une référence");
		$item->setRoute("hopitalnumerique_reference_reference_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Afficher une référence");
		$item->setRoute("hopitalnumerique_reference_reference_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer une référence");
		$item->setRoute("hopitalnumerique_reference_reference_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(4);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un établissement");
		$item->setRoute("hopitalnumerique_etablissement_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Voir un établissement");
		$item->setRoute("hopitalnumerique_etablissement_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer un établissement");
		$item->setRoute("hopitalnumerique_etablissement_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un email");
		$item->setRoute("nodevo_mail_mail_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Voir l'email");
		$item->setRoute("nodevo_mail_mail_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer un email");
		$item->setRoute("nodevo_mail_mail_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des médias");
		$item->setRoute("hopital_numerique_media_homepage");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des objets ANAP");
		$item->setRoute("hopitalnumerique_objet_objet");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(true);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter un objet");
		$item->setRoute("hopitalnumerique_objet_objet_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer un objet");
		$item->setRoute("hopitalnumerique_objet_objet_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Afficher un objet");
		$item->setRoute("hopitalnumerique_objet_objet_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Liste des contractualisations");
		$item->setRoute("hopitalnumerique_user_contractualisation");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(1);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Fiche des contractualisations");
		$item->setRoute("hopitalnumerique_user_contractualisation_show");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(2);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Editer une contractualisation");
		$item->setRoute("hopitalnumerique_user_contractualisation_edit");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(3);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Ajouter une contractualisation");
		$item->setRoute("hopitalnumerique_user_contractualisation_add");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("");
		$item->setIcon("");
		$item->setDisplay(false);
		$item->setDisplayChildren(false);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(4);

        $manager->persist($item);


		$item = new Item();
		$item->setName("Gestion des contenus");
		$item->setRoute("");
		$item->setRouteParameters("");
		$item->setRouteAbsolute(false);
		$item->setUri("javascript:;");
		$item->setIcon("fa fa-picture-o");
		$item->setDisplay(true);
		$item->setDisplayChildren(true);
		$item->setRole("IS_AUTHENTICATED_ANONYMOUSLY");
		$item->setOrder(5);

        $manager->persist($item);



        $manager->flush();
    }
}