<?php

namespace Teebb\SBAdmin2Bundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;
use Teebb\SBAdmin2Bundle\Event\ConfigureMenuEvent;

/**
 * Teebb SBAdmin menu builder.
 */
class MenuBuilder
{
    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    private $sbadmin2Config;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var MenuProviderInterface
     */
    private $provider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        TeebbSBAdmin2ConfigInterface $sbadmin2Config,
        FactoryInterface $factory,
        MenuProviderInterface $provider,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->sbadmin2Config = $sbadmin2Config;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
        dd($this->sbadmin2Config);
    }

    /**
     * Builds sidebar menu.
     *
     * @return ItemInterface
     */
    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->sbadmin2Config->getAdminGroups() as $groupName => $group) {

//            $extras = [
//                'icon' => $group['icon'] ?? "123",
//                'label_catalogue' => $group['label_catalogue'] ?? 'abc',
////                'roles' => $group['roles'],
//            ];
//
//            $menuProvider = $group['provider'] ?? 'teebb_group_menu';
//            $subMenu = $this->provider->get(
//                $menuProvider,
//                [
//                    'name' => $groupName,
//                    'group' => $group,
//                ]
//            );
//
//            $subMenu = $menu->addChild($subMenu);
//            $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));
        }


        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch($event, ConfigureMenuEvent::SIDEBAR);

        return $event->getMenu();
    }
}
