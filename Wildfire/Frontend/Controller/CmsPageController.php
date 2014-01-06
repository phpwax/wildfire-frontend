<?php
namespace Wildfire\Frontend\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wildfire\Frontend\Model\Content;

class CmsPageController {

  public $db;
  public $renderer;
  public $model = [];
  public $context;

  public function __construct($db = false, $renderer = false) {
    $this->db = $db;
    $this->renderer = $renderer;
  }

  public function model() {
    if(!isset($this->model["content"])) return new Content($this->db);
    else return $this->model["content"];
  }


  public function render($request) {

    switch($request->getPathInfo()) {
      case "/":        return new Response($this->renderer->render('home.html'));     break;
      case "/example": return new Response($this->renderer->render('example.html'));  break;
      case "/404":     return new Response($this->renderer->render('404.html'));      break;
      case "/error":   return new Response($this->renderer->render('error.html'));    break;
      default : return $this->cms($request);
    }
  }

  /**
   * Sets up the template context for a cms page.
   *
   * @return void
   **/

  public function context($request) {
    $slug = $request->getPathInfo();
    $content_row = $this->model()->getContent($slug, $request->get("preview"));
    if(!$content_row) $this->context = false;
    else $this->context = $content_row;
  }

  /**
   * Decides on a template from the context and returns a rendered template.
   *
   * @return Response
   **/

  public function template() {

  }


  public function cms($request) {
    $slug = $request->getPathInfo();
    $content_row = $this->model()->getContent($slug, $request->get("preview"));
    if(!$content_row) return false;
    return new Response($this->renderer->render('cms.html',$content_row));
  }


}