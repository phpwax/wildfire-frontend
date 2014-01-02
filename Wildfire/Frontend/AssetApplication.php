<?php

namespace Wildfire\Frontend;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * A Compatibility Middleware class designed to handle asset serving for Wildfire.
 * In development mode, paths such as /stylesheets/wildfire/file.css need to redirect to
 * vendor/phpwax/wildfire.interface/resources/public/stylesheets/wildfire/file.css
 *
 * This is an otherwise transparent middleware that only redirects on match.
 * The application can also have additional matches pushed onto the stack.
 *
 **/

class AssetApplication implements HttpKernelInterface  {

  public $app;
  public $matchers;
  public $basedir;

  public function __construct(HttpKernelInterface $app, $basedir = null) {
    $this->app = $app;
    $this->matchers = new \SplStack;
    $this->basedir = $basedir;
  }


  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
    foreach ($this->matchers as $match) {
      $route = $request->getPathInfo();
      $from = $match[0];
      if(preg_match("#^".$from."#", $route)) {
        $redirect = str_replace($from, $match[1], $route);
        return new BinaryFileResponse($redirect);
      }
    }
    return $this->app->handle($request, $type, $catch);
  }

  public function addMatcher($from, $to) {
    $this->matchers->push([$from,$to]);
  }

  public function defaults() {
    $this->addMatcher("/stylesheets/wildfire/", "/vendor/phpwax/wildfire.interface/resources/public/stylesheets/wildfire/");
    $this->addMatcher("/stylesheets/wildfire.media/", "/vendor/phpwax/wildfire.media/assets/stylesheets/wildfire.media/");

    $this->addMatcher("/javascripts/wildfire/", "/vendor/phpwax/wildfire.interface/resources/public/javascripts/wildfire-content/");
    $this->addMatcher("/javascripts/wildfire-content/", "/vendor/phpwax/wildfire.content/resources/public/javascripts/wildfire/");
    $this->addMatcher("/javascripts/wildfire.media/", "/vendor/phpwax/wildfire.media/assets/javascripts/wildfire.media/");
    $this->addMatcher("/javascripts/wildfire-plugins/", "/vendor/phpwax/wildfire.interface/resources/public/javascripts/wildfire-plugins/");
  }



}