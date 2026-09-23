<?php
namespace PersonalAccount\Workers\Analytics;
use PersonalAccount\Workers\Analytics\AnalyticsBase;

class MessageMetrics extends AnalyticsBase
{
  public function __construct(object $Container)
  {
    parent::__construct($Container);
  }
  public function createTableAnalytics($nameTable = 'gi_new_metrics_dialog_turns')
  {
    if (!$this->dbWorker->checkExistanceTable($nameTable)) {
      $sqlCreate = "CREATE TABLE `$nameTable` (
        id INT NOT NULL AUTO_INCREMENT,
        id_dialog INT NOT NULL DEFAULT 0,

        id_user INT NOT NULL DEFAULT 0,
        id_user_dealer INT DEFAULT NULL,
        user_side VARCHAR(20) NOT NULL DEFAULT '',

        started_at DATETIME NOT NULL,
        ended_at DATETIME NOT NULL,

        time_from_previous_turn INT NULL,
        time_from_previous_turn_at_work_hours INT NULL,
        
        time_before_next_turn INT NULL,
        time_before_next_turn_at_work_hours INT NULL,

        is_first_response TINYINT(1) NOT NULL DEFAULT 0,

        from_message_id INT NOT NULL DEFAULT 0,
        to_message_id INT NOT NULL DEFAULT 0,

        calculated_at DATETIME NOT NULL,

        PRIMARY KEY  (id),
        -- FOREIGN KEY (id_dialog) REFERENCES gi_new_dialogues(id_dialog),
        -- FOREIGN KEY (id_user) REFERENCES gi_new_users(id_user),
        KEY idx_dialog_started (id_dialog, started_at),
        KEY idx_user_started (id_user, started_at),
        KEY idx_first_user_started (id_user, is_first_response, started_at),
        KEY idx_user_side_started (id_user, user_side, started_at),
        KEY idx_dealer_started (id_user_dealer, started_at),
        KEY idx_dealer_side_started (id_user_dealer, user_side, started_at)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sqlCreate);
    } else {
      $this->dbWorker->dropTable($nameTable);
      $this->createTableAnalytics($nameTable);
      print_r("Table $nameTable already exists \n");
    }
  }
  public function calculateTurnsAnalytics(array $messages, string $calculatedAt): array
  {
    $turns = [];
    $previousSide = null;
    $i = 1;
    foreach ($messages as $message) {
      $userSide = $this->userUtilities->checkUserAccess($message['idAuthor']);

      if ($userSide !== $previousSide) {
        $turns[$i] = $this->packTurn(
          $message,
          [
            'previousSide' => $previousSide,
            'userSide' => $userSide,
            'previousIndex' => $i - 1,
            'previousTurnStartedAt' => isset($turns[$i - 1]) ? $turns[$i - 1]['startedAt'] : null,
            'calculatedAt' => $calculatedAt
          ]
        );
        if (isset($turns[$i - 1])) {
          $turns[$i - 1]['timeBeforeNextTurn'] = $this->simpleUtilities->diffTimeNew($message['dateSend'], $turns[$i - 1]['startedAt']);
          $turns[$i - 1]['timeBeforeNextTurnAtWorkHours'] = $this->simpleUtilities->diffWorkHours($message['dateSend'], $turns[$i - 1]['startedAt']);
        }
        $previousSide = $userSide;
        $i++;
      } else {
        $turns[$i - 1]['endedAt'] = $message['dateSend'];
        $turns[$i - 1]['toMessageId'] = $message['idMessage'];
      }
    }
    return $turns;
  }
  private function packTurn(array $message, array $settings): array
  {
    $turn = [];
    $turn['idDialog'] = $message['idDialog'];
    $turn['idUser'] = $message['idAuthor'];
    $turn['idDealer'] = $this->resolveIdDealer($settings['userSide'], (int) $message['idAuthor']);
    $turn['userSide'] = $settings['userSide'];
    $turn['startedAt'] = $message['dateSend'];
    $turn['endedAt'] = $message['dateSend'];
    $turn['timeFromPreviousTurn'] = $settings['previousSide'] === null ? 0 : $this->simpleUtilities->diffTimeNew($message['dateSend'], $settings['previousTurnStartedAt']);
    $turn['timeFromPreviousTurnAtWorkHours'] = $settings['previousSide'] === null ? 0 : $this->simpleUtilities->diffWorkHours($message['dateSend'], $settings['previousTurnStartedAt']);
    $turn['timeBeforeNextTurn'] = 0;
    $turn['timeBeforeNextTurnAtWorkHours'] = 0;
    $turn['isFirstResponse'] = $settings['previousIndex'] === 1 && $settings['userSide'] === 'factory_worker' ? 1 : 0;
    $turn['fromMessageId'] = $message['idMessage'];
    $turn['toMessageId'] = $message['idMessage'];
    $turn['calculatedAt'] = $settings['calculatedAt'];
    return $turn;
  }

  private function resolveIdDealer(string $userSide, int $idUser): ?int
  {
    if ($userSide !== 'contractor') {
      return 0;
    }

    $idDealer = $this->userUtilities->getUserDealerId($idUser);
    return $idDealer !== 0 ? $idDealer : 0;
  }

  public function writeAnalyticsNightly(array $analytics, string $nameTable): void
  {
    foreach ($analytics as $item) {
      $check = $this->dbWorker->selectSimple($nameTable, 'id_dialog', $item['idDialog']);
      if (!empty($check)) {
        $condition = $this->dbUtilities->preparenSingleOperSepar('id_dialog', $item['idDialog'], ' = ', '');
        $this->dbWorker->deleteComplexQueryDB($nameTable, $condition['sql'], $condition['values']);
      }
    }
    foreach ($analytics as $item) {
      $this->dbWorker->insertDBU($nameTable, $item);
    }
  }

}