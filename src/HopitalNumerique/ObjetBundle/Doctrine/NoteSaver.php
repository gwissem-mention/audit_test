<?php
namespace HopitalNumerique\ObjetBundle\Doctrine;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\NoteManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * Constructeur.
     */
    public function __construct(SessionInterface $session, NoteReader $noteReader, NoteManager $noteManager)
    {
        $this->session = $session;
        $this->noteReader = $noteReader;
        $this->noteManager = $noteManager;
    }


    /**
     * Enregistre la note d'un objet.
     *
     * @param integer                                       $note  Note
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet    $objet Objet
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user  Utilisateur
     */
    public function saveNoteForObjet($note, Objet $objet, User $user = null)
    {
        $noteEntity = null;
        if (null !== $user) {
            $noteEntity = $this->noteManager->findOneBy(['objet' => $objet, 'user' => $user]);
        } else if ($this->noteReader->hasNoteForObjetAndUser($objet, $user)) { // Si non connecté, pas d'update
            return;
        }

        if (null === $noteEntity) {
            $noteEntity = $this->noteManager->createEmpty();
            $noteEntity->setObjet($objet);
            $noteEntity->setUser($user);
        }
        $noteEntity->setDateNote(new \DateTime());
        $noteEntity->setNote($note);

        $this->noteManager->save($noteEntity);
        $this->saveNoteSessionForObjet($note, $objet);
    }

    /**
     * Enregistre la note d'un contenu.
     *
     * @param integer                                       $note    Note
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu  $contenu Contenu
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user    Utilisateur
     */
    public function saveNoteForContenu($note, Contenu $contenu, User $user = null)
    {
        $noteEntity = null;
        if (null !== $user) {
            $noteEntity = $this->noteManager->findOneBy(['contenu' => $contenu, 'user' => $user]);
        } else if ($this->noteReader->hasNoteForContenuAndUser($contenu, $user)) { // Si non connecté, pas d'update
            return;
        }

        if (null === $noteEntity) {
            $noteEntity = $this->noteManager->createEmpty();
            $noteEntity->setContenu($contenu);
            $noteEntity->setUser($user);
        }
        $noteEntity->setDateNote(new \DateTime());
        $noteEntity->setNote($note);
        $noteEntity->setObjet($contenu->getObjet());

        $this->noteManager->save($noteEntity);
        $this->saveNoteSessionForContenu($note, $contenu);
    }


    /**
     * Enregistre la note d'un objet en session.
     *
     * @param integer                                       $note  Note
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet    $objet Objet
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user  Utilisateur
     */
    private function saveNoteSessionForObjet($note, Objet $objet)
    {
        $this->saveNoteSessionForEntity($note, 'objet', $objet->getId());
    }

    /**
     * Enregistre la note d'un contenu en session.
     *
     * @param integer                                       $note    Note
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu  $contenu Contenu
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user    Utilisateur
     */
    private function saveNoteSessionForContenu($note, Contenu $contenu)
    {
        $this->saveNoteSessionForEntity($note, 'contenu', $contenu->getId());
    }

    /**
     * Enregistre la note d'une entité en session.
     *
     * @param integer $note       Note
     * @param integer $entityType Type d'entité
     * @param integer $entityId   ID de l'entité
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
