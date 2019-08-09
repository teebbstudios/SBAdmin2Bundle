<?php


namespace Teebb\SBAdmin2Bundle\Block;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;

class CountBlockService extends AbstractBlockService
{

    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    private $adminConfig;

    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    public function __construct($name, EngineInterface $templating, TeebbSBAdmin2ConfigInterface $adminConfig, EntityManagerInterface $objectManager)
    {
        parent::__construct($name, $templating);

        $this->adminConfig = $adminConfig;

        $this->objectManager = $objectManager;

    }


    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'admin' => null,
            'label' => '',
            'translation_domain' => null,
            'icon' => 'fas fa-file-alt',
            'border' => 'border-left-primary',
            'property' => 'createAt',
            'duration' => '-1 month',
            'template' => '@TeebbSBAdmin2/Block/count_block.html.twig',
        ]);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        // merge settings
        $settings = $blockContext->getSettings();

        try {
            $admin = $this->adminConfig->getAdminByAdminCode($settings['admin']);
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
        }

        if (!$admin instanceof AdminInterface) {
            throw new \RuntimeException(sprintf('The block config admin \'%s\' is not an Admin service id.', $settings['admin']));
        }

        $startTime = strtotime($settings['duration']);

        if ($startTime === false) {
            throw new \RuntimeException(sprintf('The block config duration \'%s\' is not an php date strtotime string.', $settings['duration']));
        }

        /**@var Query $q * */
        $q = $this->objectManager->createQuery('SELECT COUNT(o) FROM ' . $admin->getEntityClass() . ' o WHERE o. ' . $settings['property'] . ' >= :startTime')
            ->setParameter('startTime', $startTime)
            ->getResult(Query::HYDRATE_SINGLE_SCALAR);;

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'count' => $q,
        ], $response);
    }


}