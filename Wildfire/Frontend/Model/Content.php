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
    $query->setParameter("url","/".$url, \PDO::PARAM_STR);

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
          ->setParameter("p", $parent_permalink, \PDO::PARAM_STR);

    return $this->execute(function() use($query){
      $this->setResult( $query->execute()->fetchAll() );
    });
  }



  /**
   * undocumented function
   *
   * @return (string)$page_type
   **/


  public function getPageType($row, $depth=4) {

    // If it's set already on the specific row, don't go further up the tree
    if(strlen($row->page_type)>0) return $row->page_type;

    $query = $this->db->createQueryBuilder();
    for($i=1; $i<=$depth; $i++) {
      $query->addSelect("t".$i.".page_type as lev".$i);
    }
    $query->from("wildfire_content", "t1");

    for($i=2; $i<=$depth; $i++) {
      $query->leftjoin("t1", "wildfire_content","t".$i, "t".$i.".id", "t".($i-1).".parent_id");
    }
    $query->where("t1.id = :c")->setParameter("c",$row->id);
    $tree = $query->execute()->fetch();
    foreach($tree as $node) if(strlen($node)>1) return $node;
    return false;
  }



}
