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
    $this->basedir = rtrim($basedir, "/");
    $this->defaults();
  }


  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
    foreach ($this->matchers as $match) {
      $route = $request->getPathInfo();
      $from = $match[0];
      if(preg_match("#^".$from."#", $route)) {
        $redirect = $this->basedir.str_replace($from, $match[1], $route);
        $response = new BinaryFileResponse($redirect);
        if(isset($match[2])) $response->headers->set('Content-Type', $match[2]);
        $response->prepare($request);
        return $response;
      }
    }
    return $this->app->handle($request, $type, $catch);
  }

  public function addMatcher($from, $to, $content_type= null) {
    $this->matchers->push([$from,$to, $content_type]);
  }

  public function defaults() {
    $this->addMatcher("/stylesheets/wildfire/",             "/vendor/phpwax/wildfire.interface/resources/public/stylesheets/wildfire/",     "text/css");
    $this->addMatcher("/stylesheets/wildfire.media/",       "/vendor/phpwax/wildfire.media/assets/stylesheets/wildfire.media/",             "text/css");
    $this->addMatcher("/stylesheets/wildfire.formbuilder/", "/vendor/phpwax/wildfire.formbuilder/assets/stylesheets/wildfire.formbuilder/", "text/css");

    $this->addMatcher("/javascripts/wildfire/",             "/vendor/phpwax/wildfire.interface/resources/public/javascripts/wildfire/",         "text/javascript");
    $this->addMatcher("/javascripts/wildfire-content/",     "/vendor/phpwax/wildfire.content/resources/public/javascripts/wildfire-content/",   "text/javascript");
    $this->addMatcher("/javascripts/wildfire.media/",       "/vendor/phpwax/wildfire.media/assets/javascripts/wildfire.media/",                 "text/javascript");
    $this->addMatcher("/javascripts/wildfire-plugins/",     "/vendor/phpwax/wildfire.interface/resources/public/javascripts/wildfire-plugins/", "text/javascript");
    $this->addMatcher("/tinymce/",                          "/vendor/phpwax/wildfire.interface/resources/public/tinymce/",                      "text/javascript");
    $this->addMatcher("/javascripts/wildfire.formbuilder/", "/vendor/phpwax/wildfire.formbuilder/assets/javascripts/wildfire.formbuilder/",     "text/javascript");

    $this->addMatcher("/images/wildfire/",              "/vendor/phpwax/wildfire.interface/resources/public/images/wildfire/");
    $this->addMatcher("/fonts/wildfire/",               "/vendor/phpwax/wildfire.interface/resources/public/fonts/wildfire/");
  }



}