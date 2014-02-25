<?php

namespace Nodevo\MenuBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nodevo\MenuBundle\Entity\Menu;

class LoadMenuData implements FixtureInterface
{
	/**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
		$menu = new Menu();
		$menu->setName("Menu Admin");
		$menu->setAlias("menu-admin");
		$menu->setCssClass("acc-menu");
		$menu->setCssId("sidebar");
		$menu->setLock(true);

        $manager->persist($menu);


		$menu = new Menu();
		$menu->setName("Neww menu");
		$menu->setAlias("ezrzerezrrze");
		$menu->setCssClass("");
		$menu->setCssId("");
		$menu->setLock(false);

        $manager->persist($menu);



        $manager->flush();
    }
}