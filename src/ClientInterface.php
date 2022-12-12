<?php
namespace AeroInvoice;

interface ClientInterface
{
    public function buildInvoice($swno,$saleTax,$custName,$custType,$invType,$billType,
                                 $specialRedFlag,$operationCode,$kpy,array $orders,array $options = []);

    public function getInvoice($swno);

    public function getKPYL();

    public function redSubmit($fpdm, $fphm, $redcode);

    public function deleteBill($swno);

    public function changeInvoiceInfo($invCode, $reSend, $options = []);
}