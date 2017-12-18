<?php

namespace HopitalNumerique\ObjetBundle\Doctrine;

use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Note;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Event\NoteEvent;
use HopitalNumerique\ObjetBundle\Manager\NoteManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service enregistrant les notes.
 */
class NoteSaver
{
    /**
     * @var string Nom de la session
     */
    const NOTE_SESSION = 'hn_45xg96_note';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\ObjetBundle\Doctrine\NoteReader NoteReader
     */
    private $noteReader;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\NoteManager NoteManager
     */
    private $noteManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructeur.
     *
     * @param SessionInterface $session
     * @param NoteReader $noteReader
     * @param NoteManager $noteManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(SessionInterface $session, NoteReader $noteReader, NoteManager $noteManager, EventDispatcherInterface $dispatcher)
    {
        $this->session = $session;
        $this->noteReader = $noteReader;
        $this->noteManager = $noteManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Enregistre la note d'un objet.
     *
     * @param int $note Note
     * @param Objet $objet Objet
     * @param User|null $user Utilisateur
     * @param string|null $commentaire
     * @param string|null $ip
     */
    public function saveNoteForObjet($note, Objet $objet, User $user = null, $commentaire = null, $ip = null)
    {
        $noteEntity = null;
        if (null !== $user) {
            /** @var Note $noteEntity */
            $noteEntity = $this->noteManager->findOneBy(['objet' => $objet, 'user' => $user]);
        }

        if (null === $noteEntity) {
            $noteEntity = $this->noteManager->createEmpty();
            $noteEntity->setObjet($objet);
            $noteEntity
                ->setIp($ip)
            ;

            if ($user) {
                $noteEntity->setUser($user);
            }
        }
        $noteEntity->setDateNote(new \DateTime());
        $noteEntity->setNote($note);
        $noteEntity->setComment($commentaire);

        $this->noteManager->save($noteEntity);
        $this->dispatcher->dispatch(Events::OBJECT_NOTED, new NoteEvent($noteEntity));
        $this->saveNoteSessionForObjet($note, $objet);
    }

    /**
     * Enregistre la note d'un contenu.
     *
     * @param int $note Note
     * @param Contenu $contenu Contenu
     * @param User|null $user Utilisateur
     * @param string|null $commentaire
     * @param string|null $ip
     */
    public function saveNoteForContenu($note, Contenu $contenu, User $user = null, $commentaire = null, $ip = null)
    {
        $noteEntity = null;
        if (null !== $user) {
            /** @var Note $noteEntity */
            $noteEntity = $this->noteManager->findOneBy(['contenu' => $contenu, 'user' => $user]);
        }

        if (null === $noteEntity) {
            $noteEntity = $this->noteManager->createEmpty();
            $noteEntity
                ->setContenu($contenu)
                ->setIp($ip)
            ;

            if ($user) {
                $noteEntity->setUser($user);
            }
        }
        $noteEntity->setDateNote(new \DateTime());
        $noteEntity->setNote($note);
        $noteEntity->setObjet($contenu->getObjet());
        $noteEntity->setComment($commentaire);

        $this->noteManager->save($noteEntity);
        $this->dispatcher->dispatch(Events::OBJECT_NOTED, new NoteEvent($noteEntity));
        $this->saveNoteSessionForContenu($note, $contenu);
    }

    /**
     * Enregistre la note d'un objet en session.
     *
     * @param int                                           $note  Note
     * @param Objet $objet Objet
     * @param User|null $user  Utilisateur
     */
    private function saveNoteSessionForObjet($note, Objet $objet)
    {
        $this->saveNoteSessionForEntity($note, 'objet', $objet->getId());
    }

    /**
     * Enregistre la note d'un contenu en session.
     *
     * @param int                                           $note    Note
     * @param Contenu $contenu Contenu
     * @param User|null $user    Utilisateur
     */
    private function saveNoteSessionForContenu($note, Contenu $contenu)
    {
        $this->saveNoteSessionForEntity($note, 'contenu', $contenu->getId());
    }

    /**
     * Enregistre la note d'une entité en session.
     *
     * @param int $note       Note
     * @param int $entityType Type d'entité
     * @param int $entityId   ID de l'entité
     */
    private function saveNoteSessionForEntity($note, $entityType, $entityId)
    {
        $noteSession = $this->session->get(self::NOTE_SESSION, []);

        if (!array_key_exists($entityType, $noteSession)) {
            $noteSession[$entityType] = [];
        }

        if (!array_key_exists($entityId, $noteSession[$entityType])) {
            $noteSession[$entityType][$entityId] = $note;
        }

        $this->session->set(self::NOTE_SESSION, $noteSession);
    }
}
