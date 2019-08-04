<?php


namespace Teebb\SBAdmin2Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\SubmitButtonTypeInterface;

class FilterButtonType extends AbstractType implements SubmitButtonTypeInterface
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['clicked'] = $form->isClicked();
    }

    public function getParent()
    {
        return SubmitType::class;
    }

    public function getBlockPrefix()
    {
        return 'teebb_admin_filter_button';
    }
}