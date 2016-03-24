<?php
namespace HopitalNumerique\ObjetBundle\Doctrine;

use HopitalNumerique\ObjetBundle\Manager\NoteManager;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service récupérant les notes.
 */
class NoteReader
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\NoteManager NoteManager
     */
    private $noteManager;


    /**
     * Constructeur.
     */
    public function __construct(SessionInterface $session, NoteManager $noteManager)
    {
        $this->session = $session;
        $this->noteManager = $noteManager;
    }


    /**
     * Retourne la note.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet    $objet Objet
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user  Utilisateur connecté ou NULL si pas connecté
     * @return integer|null Note
     */
    public function getNoteByObjetAndUser(Objet $objet, User $user = null)
    {
        if (null !== $user) {
            $note = $this->noteManager->findOneBy(['objet' => $objet, 'user' => $user]);
            if (null !== $note) {
                return $note->getNote();
            }
        }

        return $this->getNoteSessionForObjet($objet);
    }

    /**
     * Retourne la note.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu  $contenu Contenu
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user    Utilisateur connecté ou NULL si pas connecté
     * @return integer|null Note
     */
    public function getNoteByContenuAndUser(Contenu $contenu, User $user = null)
    {
        if (null !== $user) {
            $note = $this->noteManager->findOneBy(['contenu' => $contenu, 'user' => $user]);
            if (null !== $note) {
                return $note->getNote();
            }
        }

        return $this->getNoteSessionForContenu($contenu);
    }


    /**
     * Retourne si une note existe.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet    $objet Objet
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user  Utilisateur connecté ou NULL si pas connecté
     * @return boolean Si note
     */
    public function hasNoteForObjetAndUser(Objet $objet, User $user = null)
    {
        return (null !== $this->getNoteByObjetAndUser($objet, $user));
    }

    /**
     * Retourne si une note existe.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu  $contenu Contenu
     * @param \HopitalNumerique\UserBundle\Entity\User|null $user    Utilisateur connecté ou NULL si pas connecté
     * @return boolean Si note
     */
    public function hasNoteForContenuAndUser(Contenu $contenu, User $user = null)
    {
        return (null !== $this->getNoteByContenuAndUser($contenu, $user));
    }


    /**
     * Retourne la note de l'utilisateur en session.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet Objet
     * @return integer|null Note
     */
    private function getNoteSessionForObjet(Objet $objet)
    {
        return $this->getNoteSessionForEntity('objet', $objet->getId());
    }

    /**
     * Retourne la note de l'utilisateur en session.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu Contenu
     * @return integer|null Note
     */
    private function getNoteSessionForContenu(Contenu $contenu)
    {
        return $this->getNoteSessionForEntity('contenu', $contenu->getId());
    }

    /**
     * Retourne la note de l'utilisateur en session.
     *
     * @param string  $entityType Type d'entité
     * @param integer $entityId   ID de l'entité
     * @return integer|null Note
     */
    private function getNoteSessionForEntity($entityType, $entityId)
    {
        $notesSession = $this->session->get(NoteSaver::NOTE_SESSION, null);
        $hasNote = (null !== $notesSession && array_key_exists($entityType, $notesSession) && array_key_exists($entityId, $notesSession[$entityType]));

        return ($hasNote ? intval($notesSession[$entityType][$entityId]) : null);
    }
}
