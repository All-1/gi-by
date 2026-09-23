<?php
namespace PersonalAccount\Workers\Analytics;

class OrderAnalyticsQueries extends AnalyticsQueriesBase
{
  public function queryOrderStats(array $params): array
  {
    $periodSelect = $this->periodExpression($params['period'], 'receiption_date') . ' AS period';
    $rawSql = "SELECT
        point_id,
        {$periodSelect},
        COUNT(*) AS orders_count
      FROM gi_new_orders
      WHERE receiption_date BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'
        {$this->whereExcludeBOrders()}
      GROUP BY point_id, period
      ORDER BY period, point_id";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  private function whereExcludeBOrders(): string
  {
    return " AND order_name NOT LIKE BINARY '%б1%'
      AND order_name NOT LIKE BINARY '%б2%'
      AND order_name NOT LIKE BINARY '%б3%'
      AND order_name NOT LIKE BINARY '%б4%'
      AND order_name NOT LIKE BINARY '%б5%'
      AND order_name NOT LIKE BINARY '%б6%'
      AND order_name NOT LIKE BINARY '%б7%'
      AND order_name NOT LIKE BINARY '%б8%'
      AND order_name NOT LIKE BINARY '%б9%'";
  }
}
