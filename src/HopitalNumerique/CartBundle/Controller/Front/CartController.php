<?php

namespace HopitalNumerique\CartBundle\Controller\Front;

use HopitalNumerique\CartBundle\Entity\Item\CartItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @param $objectType
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, $objectType, $objectId)
    {
        $referer = $request->headers->get('referer');

        if (is_null($this->getUser())) {
            $request->getSession()->set('cartItemReferer', $referer);
            $request->getSession()->set('urlToRedirect', $request->getUri());

            return $this->redirectToRoute('account_login');
        }
        $referer = $request->getSession()->get('cartItemReferer', $referer);
        $request->getSession()->remove('cartItemReferer');

        $cartItemRepository = $this->get('hopitalnumerique_cart.repository.cart_item');

        if (!is_null($cartItemRepository->findByObjectAndOwner($objectType, $objectId, $this->getUser()))) {
            $this->addFlash('danger', $this->get('translator')->trans('notification.addToCart.error', [], 'cart'));

            return $this->redirect($referer);
        }

        $cartItem = new CartItem($objectType, $objectId, $this->getUser(), $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());
        $this->getDoctrine()->getManager()->persist($cartItem);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', $this->get('translator')->trans('notification.addToCart.success', [], 'cart'));

        return $this->redirect($referer);
    }

    /**
     * @param $objectType
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($objectType, $objectId)
    {
        $cartItemRepository = $this->get('hopitalnumerique_cart.repository.cart_item');

        if ($cartItem = $cartItemRepository->findByObjectAndOwner($objectType, $objectId, $this->getUser())) {
            $this->getDoctrine()->getManager()->remove($cartItem);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('notification.removeFromCart.success', [], 'cart'));

            return $this->redirectToRoute('account_cart');
        }

        $this->addFlash('danger', $this->get('translator')->trans('notification.removeFromCart.error', [], 'cart'));

        return $this->redirectToRoute('account_cart');
    }
}
