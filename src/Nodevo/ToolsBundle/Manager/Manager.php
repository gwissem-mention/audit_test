<?php

namespace Nodevo\ToolsBundle\Manager;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Nodevo\ToolsBundle\Validator\Constraints\Javascript;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Classe de base des Managers de la librairie.
 *
 * @author Quentin SOMAZZI
 * @copyright Nodevo
 */
abstract class Manager
{
    protected $em = null;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository = null;
    protected $class = null;

    /**
     * @var \Doctrine\Common\Cache\Cache Cache
     */
    protected $cache = null;

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine.
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository($this->class);
    }

    /**
     * Retourne sous forme d'entier le nombre d'éléments présent dans la table.
     *
     * @return int
     */
    public function count()
    {
        return $this->getRepository()->createQueryBuilder('entity')
                                     ->select('COUNT(entity)')
                                     ->getQuery()
                                     ->getSingleScalarResult();
    }

    /**
     * Retourne un tableau de données préformatées pour le grid.
     *
     * @param string|null $condition Condition de filtrage si nécessaire
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $req = $this->getRepository()->createQueryBuilder('entity');

        if (!is_null($condition)) {
            $req->where('entity.' . $condition->field . ' = :field')
                ->setParameters(['field', $condition->value]);
        }

        return $req->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Retourne la liste des éléments ordonnées sur la champ order par défaut.
     *
     * @param string $field Field de tri par défaut
     * @param string $type  Type de tri (ADC or DESC)
     *
     * @return array
     */
    public function findAllOrdered($field = 'order', $type = 'ASC')
    {
        return $this->getRepository()->createQueryBuilder('entity')
                                     ->orderBy('entity.' . $field, $type)
                                     ->getQuery()
                                     ->getResult();
    }

    /**
     * Retourne les contraintes des validators.
     *
     * @param ValidatorInterface $validator Validator Symfony
     *
     * @return array
     */
    public function getConstraints(ValidatorInterface $validator)
    {
        $metadata = $validator->getMetadataFactory()->getMetadataFor($this->class);
        $constraints = [];

        foreach ($metadata->members as $field) {
            $element = $field[0];

            foreach ($element->constraints as $constraint) {
                if ($constraint instanceof Javascript) {
                    $constraints[$element->property]['class'] = $constraint->class;
                }
                if ($constraint instanceof Javascript && $constraint->mask) {
                    $constraints[$element->property]['mask'] = $constraint->mask;
                }
                if ($constraint instanceof Length && $constraint->max) {
                    $constraints[$element->property]['maxlength'] = $constraint->max;
                }
            }
        }

        return $constraints;
    }

    /**
     * Met en place le cache.
     *
     * @param Cache $cache Cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Récupère l'objet cache.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Retourne la liste des éléments filtrés par le tableau de critères.
     *
     * @param array $criteria Le tableau de critères array('field' => value)
     * @param array $orderBy  Order by
     * @param int   $limit    Limit
     * @param int   $offset   Offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Retourne la liste des éléments filtrés par le tableau de critères et indexés par leur ID.
     *
     * @param array $criteria Le tableau de critères array('field' => value)
     * @param array $orderBy  Order by
     * @param int   $limit    Limit
     * @param int   $offset   Offset
     *
     * @return array
     */
    public function findByIndexedById(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $entitiesById = [];

        foreach ($this->findBy($criteria, $orderBy, $limit, $offset) as $entity) {
            $entitiesById[$entity->getId()] = $entity;
        }

        return $entitiesById;
    }

    /**
     * Retourne les objets rôles présents dans le tableau de chaînes $roles.
     *
     * @param array $roles Tableau de chaînes contenant les noms des roles
     *
     * @return array
     */
    public function findIn(array $roles)
    {
        $allRoles = $this->getRepository()->findAll();
        $matchingRoles = [];

        foreach ($allRoles as $role) {
            if (in_array($role->getRole(), $roles, true)) {
                $matchingRoles[] = $role;
            }
        }

        return $matchingRoles;
    }

    /**
     * Retourne un tableau d'entités indexés par leur identifiant.
     *
     * @param array<object> $entities Entités
     *
     * @return array<mixed, object> Entités
     */
    public function getEntitiesKeyedById(array $entities)
    {
        $entitiesById = [];

        foreach ($entities as $entity) {
            $entitiesById[$entity->getId()] = $entity;
        }

        return $entitiesById;
    }

    /**
     * Enregistre l'entitée.
     *
     * @param $entity
     */
    public function save($entity)
    {
        $this->persist($entity);

        $this->flush();
    }

    public function persist($entity)
    {
        if (is_array($entity)) {
            foreach ($entity as $one) {
                $this->em->persist($one);
            }
        } else {
            $this->em->persist($entity);
        }
    }

    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Enregistre l'entitée en autorisant le forcage de l'id.
     *
     * @param object $entity
     */
    public function saveForceId($entity)
    {
        $this->em->persist($entity);

        $metadata = $this->em->getClassMetaData(get_class($entity));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->em->flush();
    }

    /**
     * Retourne 1 élément filtré selon les critères.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getRepository()->findOneBy($criteria, $orderBy);
    }

    /**
     * Retourne un élémént selon son ID.
     *
     * @param int $id ID de l'élément
     *
     * @return object
     */
    public function findOneById($id)
    {
        return $this->getRepository()->findOneById($id);
    }

    /**
     * Retourne tous les éléments sans filtres.
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Créer une nouvelle entity vide.
     *
     * @return object
     */
    public function createEmpty()
    {
        return new $this->class();
    }

    /**
     * Supprime l'entitée.
     *
     * @param object|array $entity L'entitée ou tableau d'entitée
     */
    public function delete($entity)
    {
        if (is_array($entity)) {
            foreach ($entity as $one) {
                $this->em->remove($one);
            }
        } else {
            $this->em->remove($entity);
        }

        $this->em->flush();
    }

    /**
     * Export CSV du grid selon les colonnes.
     *
     * @param array  $colonnes      Liste des colonnes à exporter
     * @param array  $datas         Tableau de données
     * @param string $filename      Nom du fichier CSV exporté
     * @param [type] $kernelCharset [description]
     *
     * @return Response
     */
    public function exportCsv($colonnes, $datas, $filename, $kernelCharset)
    {
        // Array to csv (copy from APY\DataGridBundle\Grid\Export\DSVExport.php)
        $outstream = fopen('php://temp', 'r+');

        //Ajout de la colonne d'en-têtes
        $firstLine = array_values($colonnes);
        fputcsv($outstream, $firstLine, ';', '"');

        //creation du FlatArray pour la conversion en CSV
        $keys = array_keys($colonnes);
        $flatArray = [];
        foreach ($datas as $data) {
            $ligne = [];
            foreach ($keys as $key) {
                //cas Tableau
                if (is_array($data)) {
                    $val = array_key_exists($key, $data) ? $data[$key] : '';
                    $ligne[] = is_null($val) ? '' : $val;
                    //Cas Objet
                } else {
                    //colonne External 2 test
                    if (strpos($key, '.') !== false) {
                        //cas des foreign colonnes : on explode sur le ':' et on vérifie la présence d'une valeur
                        $fcts = explode('.', $key);
                        $fct1 = 'get' . ucfirst($fcts[0]);
                        $tmp = call_user_func([$data, $fct1]);
                        //si il existe une valeur pour le 1er get, on tente de récupérer le second
                        if ($tmp) {
                            $fct2 = 'get' . ucfirst($fcts[1]);
                            $val = call_user_func([$tmp, $fct2]);
                            $ligne[] = is_null($val) ? '' : $val;
                        } else {
                            $ligne[] = '';
                        }
                        //simple colonne
                    } else {
                        $fct = 'get' . ucfirst($key);
                        $val = call_user_func([$data, $fct]);
                        $ligne[] = is_null($val) ? '' : $val;
                    }
                }
            }

            $flatArray[] = $ligne;
        }

        //génération du CSV
        foreach ($flatArray as $line) {
            fputcsv($outstream, $line, ';', '"');
        }

        //on replace le buffer au début pour refaire la lecture
        rewind($outstream);

        //génération du contenu
        $content = '';
        while (($buffer = fgets($outstream)) !== false) {
            $content .= $buffer;
        }

        fclose($outstream);

        // Charset and Length
        $charset = 'ISO-8859-1';
        if ($charset != $kernelCharset && function_exists('mb_strlen')) {
            $content = mb_convert_encoding($content, $charset, $kernelCharset);
            $filesize = mb_strlen($content, '8bit');
        } else {
            $filesize = strlen($content);
            $charset = $kernelCharset;
        }

        //build header
        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'text/comma-separated-values',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => $filesize,
        ];

        //return a Symfony Response
        $response = new Response($content, 200, $headers);
        $response->setCharset($charset);
        $response->expire();

        return $response;
    }

    /**
     * Retourne le repository associé.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->repository;
    }
}
