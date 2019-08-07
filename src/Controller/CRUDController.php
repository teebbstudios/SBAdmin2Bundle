<?php

namespace Teebb\SBAdmin2Bundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;
use Teebb\SBAdmin2Bundle\Templating\TemplateRegistryInterface;

class CRUDController extends AbstractFOSRestController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var TemplateRegistryInterface
     */
    protected $templateRegistry;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }


    public function configure()
    {
        $request = $this->getRequest();

        $adminCode = $request->get('_teebb_admin');

        if (!$adminCode) {
            throw new \RuntimeException(sprintf(
                'There is no `_teebb_admin` defined for the controller `%s` and the current route `%s`',
                \get_class($this),
                $request->get('_route')
            ));
        }

        $this->admin = $this->container->get('teebb.sbadmin2.config')->getAdminByAdminCode($adminCode);

        if (!$this->admin) {
            throw new \RuntimeException(sprintf(
                'Unable to find the admin class related to the current controller (%s)',
                \get_class($this)
            ));
        }

        $this->templateRegistry = $this->container->get('teebb.sbadmin2.template_registry');

        $rootAdmin = $this->admin;

        while ($rootAdmin->isChild()) {
            $rootAdmin->setBoolCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($request);

        //Set PaginatorInterface
        $this->paginator = $this->container->get('knp_paginator');
    }

    public function listAction(Request $request)
    {
        $this->admin->checkAccess('list');

        $listItemActions = $this->admin->getListItemActions();

        $listItemProperties = $this->admin->getListProperties();

        $filterProperties = $this->admin->getAccessFilterProperties();

        $filterParameters = $request->get('filter') ?? [];
        $orderBy = ['id' => 'DESC'];

        $pagination = $this->paginator->paginate(
            $this->admin->getConditionalQueryResults($filterParameters, $orderBy),
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('limit', 10)/*page number*/
        );

        $filterForm = $this->admin->getListFilterForm();

        if ($filterForm !== null) {
            $filterForm->handleRequest($request);
            if ($filterForm->isSubmitted() && $filterForm->isValid()) {

                $filterParameters = $filterForm->getData();

                $pagination = $this->paginator->paginate(
                    $this->admin->getConditionalQueryResults($filterParameters),
                    $request->query->getInt('page', 1)/*page number*/,
                    $request->query->getInt('limit', 10)/*page number*/
                );

            }
        }

        return $this->render($this->templateRegistry->getTemplate('base_list_full'), [
                'filterProperties' => $filterProperties,
                'filterForm' => $filterForm == null ? null : $filterForm->createView(),
                'pagination' => $pagination,
                'action' => 'List',
                'admin' => $this->admin,
                'list_item_actions' => $listItemActions,
                'list_item_properties' => $listItemProperties,
                'filterParameters' => $filterParameters
            ]
        );
    }

    public function editAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getEntityObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('edit', $object);

        $editForm = $this->admin->getForm('edit');

        $editForm->setData($object);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $objectData = $editForm->getData();

            $em = $this->admin->getObjectManager();

            $em->persist($objectData);
            $em->flush();

            $this->addFlash('success', sprintf('successfully updated!'));

            if (key_exists('button-to-list', $request->request->all())) {
                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'));
            }
        }

        return $this->render($this->templateRegistry->getTemplate('base_edit'), [
                'object' => $object,
                'action' => 'Edit',
                'admin' => $this->admin,
                'form' => $editForm->createView(),
            ]
        );
    }

    public function createAction(Request $request)
    {
        $this->admin->checkAccess('create');

        $createForm = $this->admin->getForm('create');

        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {

            $objectData = $createForm->getData();

            $em = $this->admin->getObjectManager();

            $em->persist($objectData);
            $em->flush();

            $this->addFlash('success', sprintf('successfully created!'));

            if (key_exists('button-to-list', $request->request->all())) {
                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'));
            } elseif (key_exists('button-to-new', $request->request->all())) {
                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('create'), [], 301);
            }

        }

        return $this->render($this->templateRegistry->getTemplate('base_edit'), [
                'action' => 'Create',
                'admin' => $this->admin,
                'form' => $createForm->createView(),
            ]
        );
    }

    public function deleteAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getEntityObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('delete', $object);

        if ('DELETE' === $request->getMethod()) {
            if ($this->isCsrfTokenValid('teebb.delete', $request->get('_csrf_token'))) {

                $em = $this->admin->getObjectManager();

                $em->remove($object);

                $em->flush();

                $this->addFlash('success', sprintf('successfully delete!'));

                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'));
            }
        }

        return $this->render($this->templateRegistry->getTemplate('delete'), [
                'action' => 'Delete',
                'admin' => $this->admin,
                'object' => $object
            ]
        );
    }

    public function batchAction(Request $request)
    {
        $filterParameters = $request->query->all();

        if ($request->getMethod() !== 'POST') {
            throw $this->createNotFoundException(sprintf('Invalid request type "%s", POST expected', $request->getMethod()));
        }

        if (!$this->isCsrfTokenValid('teebb.batch', $request->request->get('_csrf_token'))) {
            throw new HttpException(400, 'The csrf token is not valid, CSRF attack?');
        } else {
            $confirmation = $request->get('confirmation', false);

            if ($data = json_decode((string)$request->get('data'), true)) {
                $action = $data['action'];
                $idx = $data['idx'];
                $allElements = $data['all_elements'];
                $request->request->replace(array_merge($request->request->all(), $data));
            } else {
                $request->request->set('idx', $request->get('idx', []));
                $request->request->set('all_elements', $request->get('all_elements', false));

                $action = $request->get('action');
                $idx = $request->get('idx');
                $allElements = $request->get('all_elements');
                $data = $request->request->all();

                unset($data['_csrf_token']);
            }

            $batchActions = $this->admin->getBatchActions();
            foreach ($batchActions as $batchAction) {
                if (!\in_array($action, $batchAction)) {
                    throw new \RuntimeException(sprintf('The `%s` batch action is not defined', $action));
                }
            }

            $camelizedAction = Inflector::classify($action);
            $isRelevantAction = sprintf('batchAction%sIsRelevant', $camelizedAction);

            if (method_exists($this, $isRelevantAction)) {
                $nonRelevantMessage = \call_user_func([$this, $isRelevantAction], $idx, $allElements, $request);
            } else {
                $nonRelevantMessage = 0 !== \count($idx) || $allElements; // at least one item is selected
            }

            if (!$nonRelevantMessage) { // default non relevant message (if false of null)
                $nonRelevantMessage = 'Action aborted. No items were selected.';
            }

            if (true !== $nonRelevantMessage) {
                $this->addFlash('warning', $nonRelevantMessage);

                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'), $filterParameters);
            }

            if ('ok' !== $confirmation) {

                $template = $this->templateRegistry->getTemplate('batch_confirmation');

                return $this->render($template, [
                    'action' => $this->container->get('translator')->trans($camelizedAction, [], 'TeebbSBAdmin2Bundle'),
                    'admin' => $this->admin,
                    'data' => $data,
                ]);
            }

            // execute the action, batchActionXxxxx
            $finalAction = sprintf('batchAction%s', $camelizedAction);
            if (!method_exists($this, $finalAction)) {
                throw new \RuntimeException(sprintf('A `%s::%s` method must be callable', \get_class($this), $finalAction));
            }
            if (\count($idx) == 0 && !$allElements) {
                $this->addFlash('warning', 'Action aborted. No items were selected.');

                return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'), $filterParameters);
            }

            return \call_user_func([$this, $finalAction], $request);
        }
    }

    /**
     * Batch delete.
     */
    public function batchActionDelete(Request $request)
    {
        $this->admin->checkBatchActionsAccess('delete');

        $data = json_decode($request->get('data'), true);

        /**@var EntityManagerInterface $em * */
        $em = $this->admin->getObjectManager();

        if ($data['all_elements'] === 'on') {
            $filter = $request->get('filter', []);
            /**@var Query $query * */
            $query = $this->admin->getConditionalQueryResults($filter);

            $batchSize = 20;
            $i = 0;

            $iterableResult = $query->iterate();
            while (($row = $iterableResult->next()) !== false) {
                $em->remove($row[0]);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all deletions.
                    $em->clear(); // Detaches all objects from Doctrine!
                }
                ++$i;
            }
            $em->flush();
        } else {
            $query = $em->createQuery('DELETE FROM ' . $this->admin->getEntityClass() . ' o WHERE o.id IN (:idx)');
            $query->setParameter('idx', $data['idx']);
            $deleteNum = $query->execute();
            if ($deleteNum !== sizeof($data['idx'])) {
                throw new \RuntimeException('Deleted count not equal selected item count.I think it does\'t happen!');
            }
        }

        $this->addFlash('success', 'Selected items have been successfully deleted.');

        return $this->redirectToRoute($this->admin->getRoutes()->getRouteName('list'), ['filter' => $request->query->get('filter')]);
    }
}