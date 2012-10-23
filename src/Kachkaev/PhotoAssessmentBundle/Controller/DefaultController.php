<?php

namespace Kachkaev\PhotoAssessmentBundle\Controller;

use Symfony\Component\Translation\IdentityTranslator;

use Symfony\Component\Routing\Router;

use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;

use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Kachkaev\PhotoAssessmentBundle\Entity\User;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="pat_default_index")
     * @Template(vars={""})
     */
    public function indexAction()
    {
    	$parameters = [
    		'userIsReturning' => false,
    		'user' => null,
    	];
	    return $this->render("PhotoAssessmentBundle:Default:index.html.twig", $parameters);
    }
    
//     * @Route("/survey/{photoSource}", defaults={"photoId" = null}, name="pat_default_survey")
//     * @Route("/survey/{photoSource}/{photoId}", name="pat_default_survey_photo")
    
    /**
     * @Route("/survey/", name="pat_default_survey")
     * @Template(vars={""})
     */
    public function surveyAction()
    {
    	$response = new Response();
    	
    	
    	// If user is not authorised, authorising
    	$user = $this->get('security.context')->getToken()->getUser();
    	if (!($user instanceof UserInterface)) {
    		$user = new User();
    		$em = $this->get("doctrine.orm.entity_manager");
    		$em->persist($user);
    		$em->flush();
    		
    		$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    		$this->get('security.context')->setToken($token);
    		
    		// write cookie for persistent session storing
    		$providerKey = 'main'; // defined in security.yml
    		$securityKey = $this->container->getParameter('remember_me_token'); // defined in security.yml
    		
    		//ManagerRe
    		
    		$userProvider = new EntityUserProvider($this->container->get('doctrine'), 'Kachkaev\PhotoAssessmentBundle\Entity\User');
    		
    		$rememberMeService = new TokenBasedRememberMeServices(array($userProvider), $securityKey, $providerKey, array(
    				'path' => '/',
    				'name' => $this->container->getParameter('remember_me_cookie'),
    				'domain' => null,
    				'secure' => false,
    				'httponly' => true,
    				'lifetime' => 315360000, // 10 years
    				'always_remember_me' => true,
    				'remember_me_parameter' => '_remember_me',
    				)
    		);
    		$rememberMeService->loginSuccess($this->container->get('request'), $response, $token);
    	}
    	
       	// Translation strings
    	$jsTranslations = [];   	
    	$translator = $this->get('translator');	
    	foreach (['answer.hts',
    			'hint.questionnaire_incomplete',
    			'hint.dashboard.access_denied',
    			'hint.dashboard.queue_extended_0',
    			'hint.dashboard.queue_extended_1',
    			'hint.dashboard.queue_extended_2',
    			'hint.dashboard.queue_extended_3',
    			'hint.dashboard.queue_extended_4',
    			'hint.dashboard.queue_extended_5',
    			'hint.dashboard.queue_extended_6',
    			'message.error_api'
    			] as $v) {
    		$jsTranslations[$v] = $translator->trans($v);
    	}
    		
    	$parameters = [
    		'jsTranslationStrings' => json_encode($jsTranslations),
    		'initialQueueSize' => $this->container->getParameter('pat.queue_initial_size')
    	];
    	
	    return $this->render("PhotoAssessmentBundle:Default:survey.html.twig", $parameters, $response);
    }
    
}
