<?php
namespace Wildfire\Frontend;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wildfire\Frontend\Controller\CmsPageController;
use Wildfire\Frontend\Helpers\CmsHelper;
use Doctrine\DBAL\DriverManager;

class Application implements HttpKernelInterface
{

    public $db;
    public $renderer;
    public $controllers;

    public $app;

    public function __construct(DriverManager $db, $renderer = false) {
        $this->db = $db;
        $this->controllers = new \SplStack();
        $this->renderer = $renderer;
    }


    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
        $this->configure();
        $this->boot();

        foreach($this->controllers as $controller) {
            $response = $controller->render($request);
            if($response instanceof Response) return $response;
        }

    }


    protected function configure() {

        // Setup twig as default renderering system
        if(!isset($this->renderer)) {
            $loader = new \Twig_Loader_Filesystem(__DIR__ . '/Templates');
            $this->renderer = new \Twig_Environment($loader);
            $this->renderer->addExtension(new CmsHelper);
        }

        // Setup controllers
        $this->controllers->push(
            new CmsPageController($this->db, $this->renderer)
        );
    }


    public function registerController($callable) {
        $this->controllers->push($callable);
    }


    /**
     * Boot method allows inheriting applications to provide any setup necessary prior to a request being handled.
     *
     * @return void
    **/
    public function boot() {}




}