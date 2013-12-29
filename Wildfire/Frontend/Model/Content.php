<?php
namespace Wildfire\Frontend\Model;
use Wax\SlimModel\Model\Base;


class Content extends Base {
  public $table = "wildfire_content";


  public function setup() {
    $this->add_include("many", ["table"=>"wildfire_media","as"=>"images"]);
  }

  public function getContent($url, $preview = false) {
    $query = $this->db->createQueryBuilder();
    $query->select("*")
          ->from("wildfire_url_map","tu")
          ->leftjoin("tu", "wildfire_content", "tc", "tu.destination_id = tc.id");
    $query->where("tu.origin_url = :url");
    if(!$preview) {$query->andwhere("tu.status = 1");}
    $query->setParameter("url","/".$url);

    return $this->execute(function() use($query){
      $this->setResult( $query->execute()->fetchAll() );
    });
  }

  public function getContentChildren($parent_permalink) {
    $query = $this->db->createQueryBuilder();
    $sub   = $this->db->createQueryBuilder();
    $sub   = $sub->select("id")->from("wildfire_content","")->where("permalink = :p")->getSQL();
    $query->select("c.*")
          ->from("wildfire_content", "c")
          ->where("c.parent_id = ($sub)")
          ->andwhere("c.status=1")
          ->setParameter("p", $parent_permalink);

    return $this->execute(function() use($query){
      $this->setResult( $query->execute()->fetchAll() );
    });
  }



}
