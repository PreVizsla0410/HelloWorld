<?php

namespace App\Controller;

use App\Entity\Todo;
use Doctrine\Persistence\ManagerRegistry; //dieses Registry ist da um die Doctrine Datenverarbeitung zu koordienieren 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\TodoType;

class TodoController extends AbstractController
{
   #[Route('/', name: 'app_todo')]
   public function index(ManagerRegistry $doctrine): Response //das ist da um eine SQL abfrage mithilfe von Doctrine zu machen 
   {

       // Here we will use ManagerRegistry to use doctrine and we will select the entity that we want to work with and we used findAll() to bring all the information from it and we will save it inside a variable named todos and the type of the result will be an array
       $todos = $doctrine->getRepository(Todo::class)->findAll(); //so wird auf den definierten Wert zurückgegriffen - findAll bringt mir alle Variablen in meiner Datenbank

       return $this->render('todo/index.html.twig',
        ['todos' => $todos]); //'todo' ist der aktuelle Schlüssel 
       //sends the result (the variable that has the result of bringing all info from our database) to the index.html.twig page
   }

    #[Route('/create', name: 'create_todo')]
   public function createTodo(Request $request, ManagerRegistry $doctrine): Response
   {
       $todo = new Todo();
       $form = $this->createForm(TodoType::class, $todo);
       $form->handleRequest($request);

/* Here we have an if statement, if we click submit and if  the form is valid we will take the values from the form and we will save them in the new variables */
       if ($form->isSubmitted() && $form->isValid()) {
           $now = new \DateTime('now');

 // taking the data from the inputs with the getData() function and assign it to the $todo variable
           $todo = $form->getData();
           $todo->setCreateDate($now);  // this field is not included in the form so we set the today date
           $em = $doctrine->getManager();
           $em->persist($todo); //em ist entityManager 
           $em->flush();

           $this->addFlash( //es ist nur eine NAchricht also addFlash ist eine Benachritung, wo ein String herauskommt
               'notice',
               'Todo Added'
               );
     
           return $this->redirectToRoute('app_todo');
       }

/* now to make the form we will add this line form->createView() and now you can see the form in create.html.twig file  */
       return $this->render('todo/create.html.twig', ['form' => $form->createView()
    ]);
   }

//    #[Route('/edit/{id}', name: 'todo_edit')]
//    public function edit($id): Response
//    {
//        return $this->render('todo/edit.html.twig');
//    }
   
 #[Route('/details/{id}', name: 'details_todo')]
 public function details($id, ManagerRegistry $doctrine): Response
 {
     $todo = $doctrine->getRepository(Todo::class)->find($id);

     return $this->render('todo/details.html.twig', ['todo' => $todo]);
    }

    #[Route('/edit/{id}', name: 'todo_edit')]
  public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
  {
      $todo = $doctrine->getRepository(Todo::class)->find($id);
      $form = $this->createForm(TodoType::class, $todo);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
          $now = new \DateTime('now');
          $todo = $form->getData();
          $todo->setCreateDate($now);
          $em = $doctrine->getManager();
          $em->persist($todo);
          $em->flush();
          $this->addFlash(
               'notice',
               'Todo Edited'
               );

          return $this->redirectToRoute('todo');
      }

      return $this->render('todo/edit.html.twig', ['form' => $form->createView()]);
  }

  #[Route('/delete/{id}', name: 'delete_todo')]
  public function deleteTodo($id, ManagerRegistry $doctrine): Response 
  {
    $todo = $doctrine->getManager()->getRepository(Todo::class)->find($id);
    $em = $doctrine->getManager();
    $em->remove($todo);
      
      $em->flush();
      $this->addFlash(
          'success',
          'Todo Removed');
      return $this->render('todo/details.html.twig',
    ["todo" => $todo]
);
  }
}