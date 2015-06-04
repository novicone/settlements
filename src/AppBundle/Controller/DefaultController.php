<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add("title")->add("email")->add("submit", "submit")
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $facade = $this->get("bill_facade");
            $id = $facade->createBill($form->get("title")->getData());

            return $this->redirectToRoute("bill/show", ["id" => $id]);
        }

        return $this->render("index.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name="bill/show")
     * @param $id
     * @return Response
     */
    public function showBillAction($id) {
        return $this->render("bill/index.twig", [
            "bill" => $this->get("bill_facade")->getBill($id)
        ]);
    }

    /**
     * @Route("/{id}/add-expense", name="bill/add-expense")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function addExpenseAction($id, Request $request) {
        $form = $this->createExpenseForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $expense = $this->formDataToExpenseInfo($data);
            $this->get("bill_facade")->addExpense($id, $expense["name"], $expense["payments"], $expense["beneficiaries"]);
            return $this->redirectToRoute("bill/show", ["id" => $id]);
        }

        return $this->render("bill/expense.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/replace-expense/{name}", name="bill/replace-expense")
     * @param $id
     * @param $name
     * @param Request $request
     * @return Response
     */
    public function replaceExpenseAction($id, $name, Request $request) {
        $expense = $this->get("bill_facade")->getExpense($id, $name);
        $form = $this->createExpenseForm($expense);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $expense = $this->formDataToExpenseInfo($data);
            $this->get("bill_facade")->replaceExpense($id, $name, $expense["name"], $expense["payments"], $expense["beneficiaries"]);
            return $this->redirectToRoute("bill/show", ["id" => $id]);
        }

        return $this->render("bill/expense.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/remove-expense/{name}", name="bill/remove-expense")
     * @param $id
     * @param $name
     * @return Response
     */
    public function removeExpenseAction($id, $name) {
        $this->get("bill_facade")->removeExpense($id, $name);
        return $this->redirectToRoute("bill/show", ["id" => $id]);
    }

    private function createExpenseForm(array $data = null) {
        $data = $data ? $this->expenseInfoToFormData($data) : null;
        $form = $this->createFormBuilder($data)
            ->add("name")->add("beneficiaries")->add("payments")->add("submit", "submit")
            ->getForm();
        return $form;
    }

    private function expenseInfoToFormData(array $expense) {
        return [
            "name" => $expense["name"],
            "beneficiaries" => implode(",", $expense["beneficiaries"]),
            "payments" => implode(",", array_map(function($payment) {
                return $payment["payer"].":".$payment["amount"];
            }, $expense["payments"]))
        ];
    }

    private function formDataToExpenseInfo(array $data) {
        $name = $data["name"];
        $beneficiaries = explode(",", $data["beneficiaries"]);
        $payments = array_map(function ($paymentString) {
            list($payer, $amount) = explode(":", $paymentString);
            return [
                "payer" => $payer,
                "amount" => $amount
            ];
        }, explode(",", $data["payments"]));
        return [
            "name" => $name,
            "beneficiaries" => $beneficiaries,
            "payments" => $payments
        ];
    }

}
