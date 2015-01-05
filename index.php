<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
require 'rb.php';


R::setup('mysql:host=localhost;
        dbname=course_rest','kiquetal','paraguay');


$app=new \Slim\Slim();






$valores=array();

for ($i=0;$i<10;$i++)
{
 $valores[]=array('id'=>rand()*10+1,'name'=>'algo');
}
$app->get('/items',function() use ($app,$valores)
 {

 $app->response->headers->set('Content-Type','application/json');
 $app->response->setBody(json_encode($valores));
 $app->response->setStatus(200);

});

$app->post('/items',function()use ($app)
{
 $body=$app->request->getBody(); 
 $object_body=json_decode($body,true);
 $object_body["aatr"]="nuevo";
 $app->response->headers->set('Content-Type','application/json');
 $app->response->setBody(json_encode($object_body));



});

$app->get('/users',function() use ($app)
{
   $users=R::getAll( 'select * from users' );
   $app->response->headers->set('Content-Type','application/json');
   $app->response->setStatus(200);
  echo json_encode($users,true);


});





$app->post('/users',function() use ($app)
{

 $header=$app->request->headers->get('Content-Type');
 if ($header!='application/json')
{
   $app->response->setStatus(400);
   $app->response->headers->set('Content-Type','application/json');
   $app->response->setBody(json_encode(array('status'=>"ERROR",'response'=>'CONTENT-TYPE MUST BE APPLICATION/JSON')));
    
}
 else
{

  $body=$app->request->getBody();
  $object=json_decode($body,true);
  $user=R::dispense('users');
 try
{
  $user->name=$object['name'];
  if ($user->name==null) throw new Exception("Debe enviar el campo nombre");
  $id=R::store($user);
  $app->response->setStatus(201);
  $app->response->headers->set('Content-Type','application/json');
  $app->response->setBody(json_encode(array('status'=>'OK','response'=>array('message'=>'New user created','code'=>$id))));
 }
catch(Exception $e)
{
$app->response->setStatus(400);
$app->response->headers->set('Content-Type','application/json');
$app->response->setBody(json_encode(array('status'=>'ERROR','response'=>array('message'=>$e->getMessage()))));
}  


}

});


$app->get("/users/:id/skills",function($id) use ($app)
{

try
{
 $user=R::findOne('users','id=:id',array('id'=>$id));
 if ($user!=null)
  {

  $app->response->setStatus(200);
  $app->response->headers->set('Content-Type','application/json');
//  $app->response->setStatus(200);
//  $app->response->setBody(json_encode(array('status'=>'OK','response'=>json_decode($user,true))));
$skills=R::getAll( 'SELECT us.id_skill,s.name FROM users_skills us JOIN skills s ON (s.id=us.id_skill)
    WHERE us.id_user = :user_id',
       array(':user_id' => $id));
  $app->response->setBody(json_encode(array('status'=>'OK','response'=>array('skills'=>$skills)))); 


 // if 



  
}
else
{
 
$app->response->headers->set('Content-Type','application/json');
$app->response->setStatus(404);
$app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'User WAS NOT FOUND')));


}
}
catch(Exception $e)
{


$app->response->headers->set('Content-Type','application/json');
$app->response->setStatus(500);
$app->response->setBody(json_encode(array('status'=>'ERROR','response'=>$e->getMessage())));



}
});


$app->delete('/users/:id/skills/:id_skill',function($id,$skill_id) use ($app)
{

   $user=R::findOne('users','id=:id',array('id'=>$id));
 // echo $user;
// return; 
if ($user!=null)
{

  $skill=R::findOne('skills','id=:id',array('id'=>$skill_id));
 
 if($skill!=null)
{
   
  $skillUser=R::getAll( 'SELECT * FROM users_skills where id_user=:id_user and id_skill=:id_skill',
       array('id_user'=>$id,'id_skill'=>$skill_id));
   if ($skillUser!=null)
{

try
{

    R::exec( 'DELETE FROM  users_skills where id_user=:id_user and id_skill=:id_skill',array('id_user'=>$id,'id_skill'=>$skill_id));

$app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(200);
  $app->response->setBody(json_encode(array('status'=>'OK','response'=>'USER HAS NOT THE SKILL ANYMORE')));

}
catch(Exception $e)
{

$app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(500);
  $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>$e->getMessage())));


}


}   
  else
  {
$app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(400);
  $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'USER HAS NOT THE DESIRED SKILLS')));
}

    



}
else
{
$app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(400);
  $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'SKILL DOES NOT EXISTS')));


}


}
else
{


  $app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(400);
  $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'USER NOT FOUND')));



}

});

$app->post('/users/:id/skills',function($id) use ($app)
{
 
 $user=R::findOne('users','id=:id',array('id'=>$id));
 // echo $user;
// return; 
if ($user!=null)
  {

 // $app->response->setStatus(200);
 // $app->response->headers->set('Content-Type','application/json');
//  $app->response->setStatus(200);
//  $app->response->setBody(json_encode(array('status'=>'OK','response'=>json_decode($user,true))));


$header=$app->request->headers->get('Content-Type');
 if ($header!='application/json')
{
   $app->response->setStatus(400);
   $app->response->headers->set('Content-Type','application/json');
   $app->response->setBody(json_encode(array('status'=>"ERROR",'response'=>'CONTENT-TYPE MUST BE APPLICATION/JSON')));

}
 else
{

try
{
  $body=$app->request->getBody();
  $object=json_decode($body,true);
  $skills=R::dispense('skills');
  $skill->name=$object['name'];
  $rowSkill=R::getRow("Select * from skills where name LIKE ?",array('%'.$object['name'].'%'));
  if($rowSkill!=null)
{

 $rowMax=$rowSkill['id'];

}
else
{
 $rowMax=R::getCell('Select max(id) from skills');



 $skillNew=R::dispense('skills');
 $skillNew->id=$rowMax+1;
 
$skillNew->name=$object['name'];

 $id_skill_new=R::store($skillNew);
 $rowMax=$rowMax+1;
}
//return; 


    R::exec( 'INSERT INTO users_skills(id_user,id_skill) VALUES('.$id.','.$rowMax.')');
  
$app->response->setStatus(201);
   $app->response->headers->set('Content-Type','application/json');
   $app->response->setBody(json_encode(array('status'=>"OK",'response'=>'New skill were added')));


 }
catch(Exception $e)
{

  $app->response->setStatus(400);
   $app->response->headers->set('Content-Type','application/json');
   $app->response->setBody(json_encode(array('status'=>"ERROR",'response'=>$e->getMessage())));
}
}
}


else
{

$app->response->headers->set('Content-Type','application/json');
$app->response->setStatus(404);
$app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'User WAS NOT FOUND')));




}



});


$app->get("/users/:id", function($id) use ($app)
{

try
{

 $user=R::findOne('users','id=:id',array('id'=>$id));
 if ($user!=null)
  {

  $app->response->headers->set('Content-Type','application/json');
  $app->response->setStatus(200);
  $app->response->setBody(json_encode(array('status'=>'OK','response'=>json_decode($user,true))));
//echo $usero

}
else
{

 $app->response->headers->set('Content-Type','application/json');
 $app->response->setStatus(404);
 $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'User not found')));



}

}
catch(Exception $e)
{
 $app->response->headers->set('Content-Type','application/json');
 $app->response->setStatus(500);
 $app->response->setBody(json_encode(array('status'=>'ERROR','repsonse'=>$e->getMessage())));


}

});


$app->delete("/users/:id",function($id) use ($app)
{

 $user=R::findOne('users','id=:id',array('id'=>$id));

  if($user!=null){
    // echo json_encode($user);

try
{
 R::trash($user);
 $app->response->headers->set('Content-Type','application/json');
 $app->response->setStatus(200);
 $app->response->setBody(json_encode(array('status'=>'OK','response'=>'The user was deleted')));
}
catch(Exception $e)
{

 $app->response->headers->set('Content-Type','application/json');
 $app->response->setStatus(500);
 $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>$e->getMessage())));


}




}
  else
{
  
  $app->response->headers->set('Content-type','application/json');
  $app->response->setStatus(400);
  $app->response->setBody(json_encode(array('status'=>'ERROR','response'=>'USER NOT FOUND')));
}

});


$app->get('/items/all',function() use ($app,$valores)
{

  $accept=$app->request->headers->get('Accept');
  if ($accept=='text/xml')
{
$app->response->headers->set('Content-Type','text/xml');
$x=new SimpleXMLElement("<root/>");
$items=$x->addChild("items");
 foreach($valores as $k=>$v)
{

 $item=$items->addChild('item'); 
 $item->addChild('id',$v['id']);
 $item->addChild('name',$v['name']);

}
echo $x->asXml();

}
else
    {
    $app->response->headers->set('Content-Type','application/json');
//    echo json_encode(array('root'=>$valores));
$items=array(); 
 foreach($valores as $k=>$v)
{
$items[]=array('id'=>$v['id'],'name'=>$v['name']);
}
echo json_encode(array('root'=>array('items'=>$items)));

}

});


$app->notFound(function () use ($app) {
//    $app->render('error.php');
  echo 'NOT IMPLEMENTED YET. ';
});

$app->run();


?>
