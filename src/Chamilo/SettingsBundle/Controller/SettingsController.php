<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\SettingsBundle\Controller;

use Sylius\Bundle\SettingsBundle\Controller\SettingsController as SyliusSettingsController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Chamilo\SettingsBundle\Manager\SettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class SettingsController
 * @package Chamilo\SettingsBundle\Controller
 */
class SettingsController extends SyliusSettingsController
{

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     * @return array
     */
    public function indexAction()
    {
        $manager = $this->getSettingsManager();
        $schemas = $manager->getSchemas();
        return array('schemas' => $schemas);
    }

    /**
     * Edit configuration with given namespace.
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param string  $namespace
     *
     * @return Response
     */
    public function updateAction(Request $request, $namespace)
    {
        $manager = $this->getSettingsManager();
        $settings = $manager->loadSettings($namespace);

        $form = $this
            ->getSettingsFormFactory()
            ->create($namespace)
        ;

        $form->setData($settings);
        if ($form->handleRequest($request)->isValid()) {
            $messageType = 'success';
            try {
                $manager->saveSettings($namespace, $form->getData());
                $message = $this->getTranslator()->trans('sylius.settings.update', array(), 'flashes');
            } catch (ValidatorException $exception) {
                $message = $this->getTranslator()->trans($exception->getMessage(), array(), 'validators');
                $messageType = 'error';
            }
            $request->getSession()->getBag('flashes')->add($messageType, $message);

            if ($request->headers->has('referer')) {
                return $this->redirect($request->headers->get('referer'));
            }
        }
        return $this->render(
            'ChamiloSettingsBundle:Settings:default.html.twig',
            array(
                'settings' => $settings,
                'form'     => $form->createView()
            )
        );
    }

    /**
     * @return SettingsManager
     */
    protected function getSettingsManager()
    {
        return $this->get('chamilo.settings.manager');
    }
}
