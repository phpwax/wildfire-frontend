<?php
namespace Wildfire\Frontend\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wildfire\Frontend\Model\Content;

class CmsPageController {

  public $db;
  public $renderer;
  public $model = [];

  public function __construct($db = false, $renderer = false) {
    $this->db = $db;
    $this->renderer = $renderer;
  }

  public function model() {
    if(!$this->model["content"]) return new Content($this->db);
    else return $this->model["content"];
  }


  public function render($request) {
    if($response = $this->cms($request)) return $response;
  }


  public function cms($request) {
    $slug = $request->getPathInfo();
    $content_row = $this->model()->fetch($slug, $request->get("preview"));
    if(!$content_row) return false;
    return new Response($this->renderer->render('cms.html',$content_row));
  }

}