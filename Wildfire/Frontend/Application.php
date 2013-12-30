<?php
namespace Wildfire\Frontend;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Wildfire\Frontend\Helpers\CmsHelper;

class Application implements HttpKernelInterface  {

  public $app;
  public $controllers = [];

  public function __construct(HttpKernelInterface $app, $options = []) {
    $this->app = $app;
  }


  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true) {
    $this->configure();
    $this->boot();
    foreach($this->controllers as $controller) {
      $response = $controller->render($request);
      if($response instanceof Response) return $response;
    }
    return $this->app->handle($request, $type, $catch);
  }

  protected function configure() {
    // Setup database connection if not already set
    if(!$this->db) {
      $this->db = DriverManager::getConnection([
          'dbname' =>   $this->config["db.dbname"],
          'user' =>     $this->config["db.user"],
          'password'=>  $this->config["db.password"],
          'host' =>     $this->config["db.host"],
          'driver' =>   $this->config["db.driver"],
        ], new Configuration()
      );
    }


    // Setup twig as default renderering system
    if(!$this->renderer) {
      $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
      $this->renderer = new \Twig_Environment($loader);
      $this->renderer->addExtension(new CmsHelper);
    }

    // Setup controllers
    $this->setController("cms",new Controller\CmsPageController($this->db, $this->renderer), false);

  }

  public function setController($name, $callable, $overwrite = true) {
    if(!isset($this->controllers[$name])) $this->controllers[$name] = $callable;
    elseif($overwrite) $this->controllers[$name] = $callable;
  }

  /**
   * Boot method allows inheriting applications to provide any setup necessary prior to a request being handled.
   *
   * @return void
   **/
  public function boot() {}






}