<?php
namespace Nodevo\AdminBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Classe de base des Managers de la librairie
 * 
 * @author Quentin SOMAZZI
 * @copyright Nodevo 
 */
abstract class Manager
{
    protected $_em         = null;
    protected $_repository = null;
    protected $_class      = null;

    /**
     * @var \Doctrine\Common\Cache\Cache Cache
     */
    protected $_cache = null;

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct( EntityManager $em )
    {
        $this->_em         = $em;
        $this->_repository = $this->_em->getRepository( $this->_class );
    }

    /**
     * Retourne sous forme d'entier le nombre d'éléments présent dans la table
     *
     * @return integer
     */
    public function count()
    {
        return $this->getRepository()->createQueryBuilder('entity')
                                     ->select('COUNT(entity)')
                                     ->getQuery()
                                     ->getSingleScalarResult();
    }

    /**
     * Retourne un tableau de données préformatées pour le grid
     *
     * @param string|null $condition Condition de filtrage si nécessaire
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $req = $this->getRepository()->createQueryBuilder('entity');
        
        if ( !is_null($condition) ) {
            $req->where('entity.' . $condition->field . ' = :field' )
                ->setParameters('field', $condition->value);
        }
        
        return $req->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }

    /**
     * Retourne la liste des éléments ordonnées sur la champ order par défaut
     *
     * @param  string $field Field de tri par défaut
     * @param  string $type  Type de tri (ADC or DESC)
     *
     * @return array
     */
    public function findAllOrdered( $field = 'order', $type = 'ASC' )
    {
        return $this->getRepository()->createQueryBuilder('entity')
                                     ->orderBy('entity.' . $field, $type )
                                     ->getQuery()
                                     ->getResult();
    }

    /**
     * Retourne les contraintes des validators
     *
     * @param Validator $validator Validator Symfony
     *
     * @return array
     */
    public function getConstraints( $validator )
    {
        $metadata    = $validator->getMetadataFactory()->getMetadataFor( $this->_class );
        $constraints = array();

        foreach($metadata->members as $field) {
            $element = $field[0];

            foreach($element->constraints as $constraint){
                if ( $constraint instanceof \Nodevo\ToolsBundle\Validator\Constraints\Javascript )
                    $constraints[ $element->property ]['class'] = $constraint->class;
                if ( $constraint instanceof \Nodevo\ToolsBundle\Validator\Constraints\Javascript && $constraint->mask )
                    $constraints[ $element->property ]['mask'] = $constraint->mask;
                if( $constraint instanceof \Symfony\Component\Validator\Constraints\Length && $constraint->max )
                    $constraints[ $element->property ]['maxlength'] = $constraint->max;
            }
        }

        return $constraints;
    }

    /**
     * Met en place le cache
     *
     * @param Cache $cache Cache
     */
    public function setCache( Cache $cache )
    {
        $this->_cache = $cache;
    }

    /**
     * Récupère l'objet cache
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Retourne la liste des éléments filtrés par le tableau de critères
     *
     * @param array $criteria Le tableau de critères array('field' => value)
     * @param array $orderBy Order by
     * @param integer $limit Limit
     * @param integer $offset Offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Enregistre l'entitée
     *
     * @param Entity|array $entity L'entitée
     *
     * @return empty
     */
    public function save( $entity )
    {
        if( is_array($entity) ){
            foreach( $entity as $one )
                $this->_em->persist( $one );
        }else
            $this->_em->persist( $entity );

        $this->_em->flush();
    }

    /**
     * Retourne 1 élément filtré selon les critères
     *
     * @param array $criteria Les critères
     *
     * @return Entity
     */
    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Retourne un élémént selon son ID
     *
     * @param integer $id ID de l'élément
     *
     * @return Entity
     */
    public function findOneById( $id )
    {
        return $this->getRepository()->findOneById($id);
    }

    /**
     * Retourne tous les éléments sans filtres
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }
    
    /**
     * Créer une nouvelle entity vide
     *
     * @return Entity
     */
    public function createEmpty()
    {
        return new $this->_class;
    }

    /**
     * Supprime l'entitée
     *
     * @param Entity|array $entity L'entitée ou tableau d'entitée
     *
     * @return empty
     */
    public function delete( $entity )
    {
        if( is_array($entity) ){
            foreach( $entity as $one )
                $this->_em->remove( $one );
        }else
            $this->_em->remove( $entity );

        $this->_em->flush();
    }

    /**
     * Export CSV du grid selon les colonnes
     *
     * @param array $colonnes Liste des colonnes à exporter
     * @param array $datas    Tableau de données
     *
     * @return Response
     */
    public function exportCsv( $colonnes, $datas, $kernelCharset )
    {
        // Array to csv (copy from APY\DataGridBundle\Grid\Export\DSVExport.php)
        $outstream = fopen("php://temp", 'r+');

        //Ajout de la colonne d'en-têtes
        $firstLine = array_values($colonnes);
        fputcsv($outstream, $firstLine, ';', '"');

        //creation du FlatArray pour la conversion en CSV
        $keys      = array_keys($colonnes);
        $flatArray = array();
        foreach($datas as $data) {
            $ligne = array();
            foreach($keys as $key) {
                //colonne External 2 test
                if( strpos($key, '.') !== false) {
                    //cas des foreign colonnes : on explode sur le ':' et on vérifie la présence d'une valeur
                    $fcts = explode('.', $key);
                    $fct1 = 'get'. ucfirst($fcts[0]);
                    $tmp  = call_user_func(array($data, $fct1 ));
                    //si il existe une valeur pour le 1er get, on tente de récupérer le second
                    if( $tmp ) {
                        $fct2    = 'get'. ucfirst($fcts[1]);
                        $val     =  call_user_func(array($tmp, $fct2 ));
                        $ligne[] = is_null($val) ? '' : $val;
                    }else
                        $ligne[] = '';
                //simple colonne
                }else{
                    $fct     = 'get'.ucfirst($key);
                    $val     = call_user_func(array($data,$fct));
                    $ligne[] = is_null($val) ? '' : $val;
                }
            }

            $flatArray[] = $ligne;
        }

        //génération du CSV
        foreach ($flatArray as $line)
            fputcsv($outstream, $line, ';', '"');

        //on replace le buffer au début pour refaire la lecture
        rewind($outstream);

        //génération du contenu
        $content = '';
        while (($buffer = fgets($outstream)) !== false)
            $content .= $buffer;

        fclose($outstream);

        // Charset and Length
        $charset = 'UTF-8';
        if ($charset != $kernelCharset && function_exists('mb_strlen')) {
            $content  = mb_convert_encoding($content, $charset, $kernelCharset);
            $filesize = mb_strlen($content, '8bit');
        } else {
            $filesize = strlen($content);
            $charset  = $kernelCharset;
        }

        //build header
        $headers = array(
            'Content-Description'       => 'File Transfer',
            'Content-Type'              => 'text/comma-separated-values',
            'Content-Disposition'       => sprintf('attachment; filename="%s"', 'export-utilisateurs.csv'),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control'             => 'must-revalidate',
            'Pragma'                    => 'public',
            'Content-Length'            => $filesize
        );

        //return a Symfony Response
        $response = new Response($content, 200, $headers);
        $response->setCharset( $charset );
        $response->expire();

        return $response;
    }

    /**
     * Retourne le repository associé
     *
     * @return Repository
     */
    protected function getRepository()
    {
        return $this->_repository;
    }
}