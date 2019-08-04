<?php


namespace Teebb\SBAdmin2Bundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigFormThemeCompilePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $form_theme_old = $container->getParameter('twig.form.resources');
        $form_theme = array_merge($form_theme_old, ['@TeebbSBAdmin2/Form/Layout/teebb_form_layout.html.twig']);

        $container->getDefinition('twig.form.engine')->replaceArgument(0, $form_theme);
    }
}