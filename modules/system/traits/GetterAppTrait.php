<?php

namespace atsilex\module\system\traits;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConsoleRunner as DBAL;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Console\ConsoleRunner as ORM;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Silex\ControllerCollection;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilderInterface;
use Twig_Environment;

/**
 * Easier to get app services.
 */
trait GetterAppTrait
{

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this['logger']) {
            $this['logger'] = new NullLogger();
        }
        return $this['logger'];
    }

    /**
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this['cache'];
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this['db'];
    }

    /**
     * @return Configuration
     */
    public function getDbConfig()
    {
        return $this['db.config'];
    }

    /**
     * @return EventManager
     */
    public function getDbEventManager()
    {
        return $this['db.event_manager'];
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager($name = 'default')
    {
        return isset($this["orm.em.{$name}"]) ? $this["orm.em.{$name}"] : $this['orm.em'];
    }

    /**
     * @param string $className
     * @param string $emName
     * @return EntityRepository
     */
    public function getRepository($className, $emName = 'default')
    {
        return $this->getEntityManager($emName)->getRepository($className);
    }

    /**
     * @return ControllerCollection
     */
    public function getControllerFactory()
    {
        return $this['controllers_factory'];
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this['routes'];
    }

    /**
     * @return Console
     */
    public function getConsole()
    {
        if (!isset($this['console']) || (null === $this['console'])) {
            $name = isset($this['site_name']) ? $this['site_name'] : 'V3K';
            $version = isset($this['site_version']) ? $this['site_version'] : 'dev';
            $this['console'] = new Console($name, $version);

            // Doctrine commands
            $this['console']->setHelperSet(DBAL::createHelperSet($this->getDb()));
            $this['console']->setHelperSet(ORM::createHelperSet($this->getEntityManager()));
            DBAL::addCommands($this['console']);
            ORM::addCommands($this['console']);

            // Our custom commands
            foreach ($this->keys() as $key) {
                if (0 === strpos($key, '@') && false !== strpos($key, '.cmd.')) {
                    $cmd = $this[$key];
                    if ($cmd instanceof Command) {
                        $this['console']->add($cmd);
                    }
                }
            }
        }

        return $this['console'];
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this['dispatcher'];
    }

    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return $this['twig'];
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this['form.factory'];
    }

    /**
     * @return CsrfTokenManager
     */
    public function getCsrfProvider()
    {
        return $this['form.csrf_provider'];
    }

    /**
     * @return SecurityContext
     */
    public function getSecurity()
    {
        return $this['security'];
    }

    /**
     * @return AuthenticationProviderManager
     */
    public function getSecurityAuthenticationManager()
    {
        return $this['security.authentication_manager'];
    }

    /**
     * @return AccessDecisionManager
     */
    public function getSecurityAccessManager()
    {
        return $this['security.access_manager'];
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this['serializer'];
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this['session'];
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this['translator'];
    }

    /**
     * @return ValidatorBuilderInterface
     */
    public function getValidatorBuilder()
    {
        return $this['validator.builder'];
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this['validator'];
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        return $this['mailer'];
    }

    /**
     * @return \Swift_Transport
     */
    public function getMailerTransport()
    {
        return $this['swiftmailer.transport'];
    }

}
