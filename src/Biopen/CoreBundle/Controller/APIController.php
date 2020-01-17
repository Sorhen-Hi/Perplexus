<?php

namespace Biopen\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Biopen\CoreBundle\Controller\GoGoController;
use Intervention\Image\ImageManagerStatic as InterventionImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class APIController extends GoGoController
{
  public function apiUiAction()
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $config = $em->getRepository('BiopenCoreBundle:Configuration')->findConfiguration();
    $protectPublicApiWithToken = $config->getApi()->getProtectPublicApiWithToken();

    $securityContext = $this->get('security.context');
    $userLoggued = $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');

    if ($protectPublicApiWithToken && !$userLoggued) {
      $this->getRequest()->getSession()->set('_security.main.target_path', 'api');
      return $this->redirectToRoute('fos_user_security_login');
    }

    if ($protectPublicApiWithToken)
    {
      $user = $securityContext->getToken()->getUser();
      if (!$user->getToken()) { $user->createToken(); $em->flush(); }
    }

    $options = $em->getRepository('BiopenGeoDirectoryBundle:Option')->findAll();
    return $this->render('BiopenCoreBundle:api:api-ui.html.twig', array('options' => $options));
  }

  public function getManifestAction()
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $config = $em->getRepository('BiopenCoreBundle:Configuration')->findConfiguration();
    $img = $config->getFavicon() ? $config->getFavicon() : $config->getLogo();
    $imageData = null;

    if ($img) {
      $imgUrl = $img->getImageUrl('512x512', 'png');
      try {
        if (!$img->isExternalFile()) $imageData = InterventionImage::make($img->calculateFilePath('512x512', 'png'));
        else $imageData = InterventionImage::make($imgUrl);
      } catch (\Exception $error) { }
    }
    if (!$imageData) {
      $imgUrl = $this->getRequest()->getUriForPath('/assets/img/default-icon.png');
      if ($this->container->get('kernel')->getEnvironment() == 'dev') {
        $imgUrl = str_replace('app_dev.php/', '', $imgUrl);
      }
      try {
        $imageData = InterventionImage::make($imgUrl);
      } catch (\Exception $error) { }
    }

    $icon = [ "src" => $imgUrl ];
    if ($imageData) {
      $icon['sizes'] = $imageData->height().'x'.$imageData->width();
      $icon['mime'] = $imageData->mime();
    }
    $shortName = $config->getAppNameShort() && strlen($config->getAppNameShort()) > 0 ? $config->getAppNameShort() : $config->getAppName();
    $responseArray = array(
      "name" => $config->getAppName(),
      "short_name" =>  str_split($shortName, 12)[0],
      "lang" => "fr",
      "start_url" => "/annuaire#/carte/autour-de-moi",
      "display" => "standalone",
      "theme_color" => $config->getPrimaryColor(),
      "background_color" => $config->getBackgroundColor(),
      "icons" => [ $icon ]
    );
    $response = new Response(json_encode($responseArray));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  public function getProjectInfoAction()
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $config = $em->getRepository('BiopenCoreBundle:Configuration')->findConfiguration();
    $img = $config->getSocialShareImage() ? $config->getSocialShareImage() : $config->getLogo();
    $imageUrl = $img ? $img->getImageUrl() : null;
    $dataSize = $em->getRepository('BiopenGeoDirectoryBundle:Element')->findVisibles(true);

    $responseArray = array(
      "name" => $config->getAppName(),
      "imageUrl" =>  $imageUrl,
      "description" => $config->getAppBaseline(),
      "dataSize" => $dataSize
    );
    $response = new Response(json_encode($responseArray));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  public function getConfigurationAction()
  {
    $odm = $this->get('doctrine_mongodb')->getManager();
    $config = $odm->getRepository('BiopenCoreBundle:Configuration')->findConfiguration();
    $defaultTileLayer = $config->getDefaultTileLayer()->getName();
    $config = json_decode(json_encode($config));

    $tileLayers = $odm->getRepository('BiopenCoreBundle:TileLayer')->findAll();

    $config->defaultTileLayer = $defaultTileLayer;
    $config->tileLayers = $tileLayers;
    $response = new Response(json_encode($config));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  public function hideLogAction($id)
  {
    $odm = $this->get('doctrine_mongodb')->getManager();
    $log = $odm->getRepository('BiopenCoreBundle:GoGoLog')->find($id);
    $log->setHidden(true);
    $odm->flush();
    $response = new Response(json_encode(['success' => true]));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  public function hideAllLogsAction()
  {
    $odm = $this->get('doctrine_mongodb')->getManager();
    $qb = $odm->createQueryBuilder('BiopenCoreBundle:GoGoLog');
    $qb->updateMany()
       ->field('type')->notEqual('update')
       ->field('hidden')->equals(false)
       ->field('hidden')->set(true)->getQuery()->execute();
    return $this->redirectToRoute('sonata_admin_dashboard');
  }

  public function hideAllMessagesAction()
  {
    $odm = $this->get('doctrine_mongodb')->getManager();
    $qb = $odm->createQueryBuilder('BiopenCoreBundle:GoGoLogUpdate');
    $qb->updateMany()
       ->field('type')->equals('update')
       ->field('hidden')->equals(false)
       ->field('hidden')->set(true)->getQuery()->execute();
    return $this->redirectToRoute('sonata_admin_dashboard');
  }
}