<?php

namespace Teebb\SBAdmin2Bundle\Menu;


use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;
use Teebb\SBAdmin2Bundle\Event\ConfigureMenuEvent;
use Teebb\SBAdmin2Bundle\Menu\Custom\TeebbMenuItem;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MenuProviderInterface
     */
    private $provider;

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
    }

    /**
     * Builds sidebar menu.
     *
     * @return ItemInterface
     */
    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem('root');
        $groups = [];
        foreach ($this->sbadmin2Config->getAdminGroups() as $groupName => $group) {
            $groups[] = $groupName;
            foreach ($group as $itemsKey => $itemsInfo) {

                if (1 === count($itemsInfo['items'])) {
                    $menuItem = $menu->addChild($this->generateMenuItem($itemsInfo['items'][0], $itemsInfo));
                } else {
                    $menuItem = $menu->addChild($this->factory->createItem($itemsInfo['label']));

                    foreach ($itemsInfo['items'] as $item) {
                        $menuItem->addChild($this->generateMenuItem($item, $itemsInfo));
                    }
                }

                $menuItem->setExtra('group', $groupName);
                $menuItem->setExtra('icon', $itemsInfo['icon'] ?? $this->sbadmin2Config->getOption('default_icon'));
                $menuItem->setExtra('translation_domain',  $itemsInfo['label_catalogue'] ?? $this->sbadmin2Config->getOption('default_label_catalogue'));

                if (isset($itemsInfo['provider'])) {

                    $extras = [
                        'icon' => $itemsInfo['icon'],
                        'translation_domain' => $itemsInfo['label_catalogue'] ?? $this->sbadmin2Config->getOption('default_label_catalogue'),
                        'roles' => $itemsInfo['roles'],
                        'group' => $groupName,
                    ];

                    $subMenu = $this->provider->get(
                        $itemsInfo['provider'],
                        [
                            'name' => $itemsKey,
                            'group' => $itemsInfo,
                        ]
                    );

                    $subMenu = $menu->addChild($subMenu);

                    $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));

                }
            }

        }
        $menu->setExtra('group', $groups);
        $menu->setExtra('translation_domain', $this->sbadmin2Config->getOption('default_label_catalogue'));

        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch($event, ConfigureMenuEvent::SIDEBAR);

        return $event->getMenu();
    }

    private function generateMenuItem(array $item, array $group): ItemInterface
    {
        if (isset($item['admin']) && !empty($item['admin']) && !isset($group['provider'])) {
//            $admin = $this->pool->getInstance($item['admin']);
//
//            $options = $admin->generateMenuUrl('list', [], $item['route_absolute']);
//            $options['extras'] = [
//                'label_catalogue' => $admin->getTranslationDomain(),
//                'admin' => $admin,
//            ];
//
//            return $this->menuFactory->createItem($admin->getLabel(), $options);
            return $this->factory->createItem($item['label']);
        }

        return $this->factory->createItem($item['label'], [
            'route' => $item['route'],
            'routeParameters' => $item['route_params'],
            'routeAbsolute' => $item['route_absolute'],
            'extras' => [
                'translation_domain' => $group['label_catalogue'] ?? $this->sbadmin2Config->getOption('default_label_catalogue'),
            ],
        ]);
    }

}