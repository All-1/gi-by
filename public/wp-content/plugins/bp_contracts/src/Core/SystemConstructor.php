<?php

namespace PersonalAccount\Core;

use PersonalAccount\Chat;
use PersonalAccount\Core\Container;
use PersonalAccount\Factory\Factory;

use PersonalAccount\Controler\InteractionInterface;
use PersonalAccount\Controler\UserControler;
use PersonalAccount\Controler\ControlerObjectsRelationship;

use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\SSHRemote;
use PersonalAccount\Workers\Mailer;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\NotificationWorker;
use PersonalAccount\Workers\UPWorker;
use PersonalAccount\Workers\ORWorker;
use PersonalAccount\Workers\InvoiceWorker;
use PersonalAccount\Workers\ContractsWorker;
use PersonalAccount\Workers\MessageWorker;
use PersonalAccount\Workers\OrdersWorker;
use PersonalAccount\Workers\ManageContractorsWorker;

use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\DialogServices;

use PersonalAccount\Workers\Analytics\DialogMetrics;
use PersonalAccount\Workers\Analytics\ManagerMetrics;
use PersonalAccount\Workers\Analytics\ContractorsMetrics;
use PersonalAccount\Workers\Analytics\MessageMetrics;
use PersonalAccount\Workers\Collector\AnalyticsCollector;
use PersonalAccount\Workers\Analytics\TurnAnalyticsQueries;
use PersonalAccount\Workers\Analytics\OrderAnalyticsQueries;
use PersonalAccount\Workers\Analytics\DialogAnalyticsQueries;
use PersonalAccount\Workers\Collector\MessageCollector;
use PersonalAccount\Workers\Collector\UserCollector;

class SystemConstructor
{
    private Chat $chat;
    private Container $container;
    public function __construct($chat)
    {
        $this->container = new Container();
        $this->chat = $chat;
        $this->container->set('Chat', $this->chat);

        $linkContainer = &$this->container;

        $CatcherBugs = new CatcherBugs();
        $this->container->set('CatcherBugs', $CatcherBugs);

        $SSHRemote = new SSHRemote(ServicesContainer: $linkContainer);
        $this->container->set('SSHRemote', $SSHRemote);

        $Mailer = new Mailer();
        $this->container->set('Mailer', $Mailer);

        $SimpleUtilities = new SimpleUtilities($linkContainer);
        $this->container->set('SimpleUtilities', $SimpleUtilities);

        $DBUtilities = new DBUtilities($linkContainer);
        $this->container->set('DBUtilities', $DBUtilities);

        $DBWorker = new DBWorker($linkContainer);
        $this->container->set('DBWorker', $DBWorker);

        $DataUtilities = new DataUtilities($linkContainer);
        $this->container->set('DataUtilities', $DataUtilities);

        $UPWorker = new UPWorker($linkContainer);
        $this->container->set('UPWorker', $UPWorker);

        $ORWorker = new ORWorker($linkContainer);
        $this->container->set('ORWorker', $ORWorker);

        $UserUtilities = new UserUtilities($linkContainer);
        $this->container->set('UserUtilities', $UserUtilities);

        $Factory = new Factory($linkContainer);
        $this->container->set('Factory', $Factory);

        $DialogServices = new DialogServices($linkContainer);
        $this->container->set('DialogServices', $DialogServices);

        $UserControler = new UserControler($linkContainer);
        $this->container->set('UserControler', $UserControler);

        $ControlerObjectsRelationship = new ControlerObjectsRelationship($linkContainer);
        $this->container->set('ControlerObjectsRelationship', $ControlerObjectsRelationship);

        $NotificationWorker = new NotificationWorker($linkContainer);
        $this->container->set('NotificationWorker', $NotificationWorker);

        $InvoiceWorker = new InvoiceWorker($linkContainer);
        $this->container->set('InvoiceWorker', $InvoiceWorker);

        $InteractionInterface = new InteractionInterface($linkContainer);
        $this->container->set('InteractionInterface', $InteractionInterface);

        $MessageWorker = new MessageWorker($linkContainer);
        $this->container->set('MessageWorker', $MessageWorker);
        
        $contractsWorker = new ContractsWorker($linkContainer);
        $this->container->set('ContractsWorker', $contractsWorker);

        $ordersWorker = new OrdersWorker($linkContainer);
        $this->container->set('OrdersWorker', $ordersWorker);

        $ManageContractorsWorker = new ManageContractorsWorker($linkContainer);
        $this->container->set('ManageContractorsWorker', $ManageContractorsWorker);

        $messageCollector = new MessageCollector($linkContainer);
        $this->container->set('MessageCollector', $messageCollector);

        $dialogMetrics = new DialogMetrics($linkContainer);
        $this->container->set('DialogMetrics', $dialogMetrics);

        $managerMetrics = new ManagerMetrics($linkContainer);
        $this->container->set('ManagerMetrics', $managerMetrics);

        $contractorsMetrics = new ContractorsMetrics($linkContainer);
        $this->container->set('ContractorsMetrics', $contractorsMetrics);

        $messageMetrics = new MessageMetrics($linkContainer);
        $this->container->set('MessageMetrics', $messageMetrics);

        $turnAnalyticsQueries = new TurnAnalyticsQueries($linkContainer);
        $this->container->set('TurnAnalyticsQueries', $turnAnalyticsQueries);

        $dialogAnalyticsQueries = new DialogAnalyticsQueries($linkContainer);
        $this->container->set('DialogAnalyticsQueries', $dialogAnalyticsQueries);

        $orderAnalyticsQueries = new OrderAnalyticsQueries($linkContainer);
        $this->container->set('OrderAnalyticsQueries', $orderAnalyticsQueries);

        $analyticsCollector = new AnalyticsCollector($linkContainer);
        $this->container->set('AnalyticsCollector', $analyticsCollector);

        $userCollector = new UserCollector($linkContainer);
        $this->container->set('UserCollector', $userCollector);
    }

    public function getContainer(): Container
    {
        $link = &$this->container;
        return $link;
    }
} 