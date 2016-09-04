<?php
function get_catalog_count($category = null){
  $category = strtolower($categroy);
  include("connection.php");
  try{
    if(!empty($category)){
      $results = $db->prepare("Select COUNT(media_id) from Media
      where LOWER(category) = ?");
      $results->bindParam(1, $category, PDO::PARAM_STR);
  }else{
    $results = $db->prepare("Select COUNT(media_id) from Media");
  }
  $results->execute();

  }catch(Exception $e){
    echo $e->getMessage();
    exit;
  }
  $count = $results->fetchColumn(0);
  return $count;
}

function full_catalog_retrieval($limit = null, $offset = 0){
    include("connection.php");
    try{
      $sql = "Select media_id,title, category,img from Media ORDER BY title";
      if(is_integer($limit)){
        $results = $db->prepare($sql. " LIMIT ? OFFSET ?");
        $results->bindParam(1,$limit, PDO::PARAM_INT);
        $results->bindParam(2,$offset, PDO::PARAM_INT);
      }else{
          $results = $db->prepare($sql);
      }

      $results->execute();
    }catch(Exception $e){
      echo $e->getMessage();
      exit;
    }
    $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}

function category_catalog_retrieval($category, $limit= null, $offset = 0){
    include("connection.php");
    $category = strtolower($category);
    try{
      $sql = "Select media_id,title,
      category,img
      from Media
      WHERE LOWER(category) =?
      ORDER BY title";
      if(is_integer($limit)){
        $results = $db->prepare($sql. " LIMIT ? OFFSET ?");
        $results->bindParam(1,$category, PDO::PARAM_STR);
        $results->bindParam(2,$limit, PDO::PARAM_INT);
        $results->bindParam(3,$offset, PDO::PARAM_INT);
      }else{
          $results = $db->prepare($sql);
          $results->bindParam(1,$category, PDO::PARAM_STR);
      }
      $results->execute();
    }catch(Exception $e){
      echo $e->getMessage();
      exit;
    }
    $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}

function random_catalog_retrieval(){
    include("connection.php");
    try{
      $results = $db->query("Select media_id,title,
      category,img
      from Media
      ORDER BY RAND()
      LIMIT 4");
    }catch(Exception $e){
      echo $e->getMessage();
      exit;
    }
    $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}

function get_array_item($id){
  include("connection.php");
  try{
    $results = $db->prepare(
    "Select Media.media_id,title, category,img, format, year, genre
    from Media
    join Genres on Media.genre_id = Genres.genre_id
    left outer join Books on Media.media_id = Books.media_id
    Where Media.media_id = ?");
    $results->bindParam(1, $id, PDO::PARAM_INT);
    $results->execute();
  }catch(Exception $e){
    echo $e->getMessage();
    exit;
  }

  $item = $results->fetch(PDO::FETCH_ASSOC);
  if(empty($item)) return $item;

  try{
    $results = $db->prepare(
    "Select fullname, role
    from Media_People
    join People on Media_people.people_id = People.people_id
    Where Media_People.media_id = ?");
    $results->bindParam(1, $id, PDO::PARAM_INT);
    $results->execute();
  }catch(Exception $e){
    echo $e->getMessage();
    exit;
  }

  while($row =  $results->fetch(PDO::FETCH_ASSOC)){
    $item[$row["role"]][] = $row["fullname"];
  }

  return $item;
}

function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item["media_id"] . "'><img src='"
        . $item["img"] . "' alt='"
        . $item["title"] . "' />"
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}

function array_category($catalog,$category) {
    $output = array();

    foreach ($catalog as $id => $item) {
        if ($category == null OR strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort,"The ");
            $sort = ltrim($sort,"A ");
            $sort = ltrim($sort,"An ");
            $output[$id] = $sort;
        }
    }

    asort($output);
    return array_keys($output);
}
