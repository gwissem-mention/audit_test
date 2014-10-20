<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

    // --- Append the init function below ---
    public function init()
    {
        date_default_timezone_set( 'Europe/Paris' );
        parent::init();
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Liip\DoctrineCacheBundle\LiipDoctrineCacheBundle(),
            new APY\DataGridBundle\APYDataGridBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Igorw\FileServeBundle\IgorwFileServeBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Nodevo\AdminBundle\NodevoAdminBundle(),
            new Nodevo\MenuBundle\NodevoMenuBundle(),
            new Nodevo\RoleBundle\NodevoRoleBundle(),
            new Nodevo\GridBundle\NodevoGridBundle(),
            new Nodevo\ToolsBundle\NodevoToolsBundle(),
            new Nodevo\AclBundle\NodevoAclBundle(),
            new Nodevo\MailBundle\NodevoMailBundle(),
            new Nodevo\ErrorsBundle\NodevoErrorsBundle(),
            new Nodevo\ContactBundle\NodevoContactBundle(),
            new Nodevo\GestionnaireMediaBundle\NodevoGestionnaireMediaBundle(),
            new Nodevo\FaqBundle\NodevoFaqBundle(),
            new HopitalNumerique\CoreBundle\HopitalNumeriqueCoreBundle(),
            new HopitalNumerique\UserBundle\HopitalNumeriqueUserBundle(),
            new HopitalNumerique\ReferenceBundle\HopitalNumeriqueReferenceBundle(),
            new HopitalNumerique\EtablissementBundle\HopitalNumeriqueEtablissementBundle(),
            new HopitalNumerique\AdminBundle\HopitalNumeriqueAdminBundle(),
            new HopitalNumerique\ObjetBundle\HopitalNumeriqueObjetBundle(),
            new HopitalNumerique\QuestionnaireBundle\HopitalNumeriqueQuestionnaireBundle(),
            new HopitalNumerique\AccountBundle\HopitalNumeriqueAccountBundle(),
            new HopitalNumerique\InterventionBundle\HopitalNumeriqueInterventionBundle(),
            new HopitalNumerique\RechercheBundle\HopitalNumeriqueRechercheBundle(),
            new HopitalNumerique\RechercheParcoursBundle\HopitalNumeriqueRechercheParcoursBundle(),
            new HopitalNumerique\RegistreBundle\HopitalNumeriqueRegistreBundle(),
            new HopitalNumerique\PublicationBundle\HopitalNumeriquePublicationBundle(),
            new HopitalNumerique\ContactBundle\HopitalNumeriqueContactBundle(),
            new HopitalNumerique\FaqBundle\HopitalNumeriqueFaqBundle(),
            new HopitalNumerique\ModuleBundle\HopitalNumeriqueModuleBundle(),
            new HopitalNumerique\PaiementBundle\HopitalNumeriquePaiementBundle(),
            new HopitalNumerique\AutodiagBundle\HopitalNumeriqueAutodiagBundle(),
            new HopitalNumerique\ForumBundle\HopitalNumeriqueForumBundle(),
            new HopitalNumerique\FlashBundle\HopitalNumeriqueFlashBundle(),
            new HopitalNumerique\GlossaireBundle\HopitalNumeriqueGlossaireBundle(),
            //-v- Bundles du Forum -v-
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new CCDNForum\ForumBundle\CCDNForumForumBundle(),
            new CCDNComponent\BBCodeBundle\CCDNComponentBBCodeBundle(),
            //-^- Bundles du Forum -^-,
            new HopitalNumerique\StatBundle\HopitalNumeriqueStatBundle(),
            new HopitalNumerique\ReportBundle\HopitalNumeriqueReportBundle(),
            new HopitalNumerique\ImportExcelBundle\HopitalNumeriqueImportExcelBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
