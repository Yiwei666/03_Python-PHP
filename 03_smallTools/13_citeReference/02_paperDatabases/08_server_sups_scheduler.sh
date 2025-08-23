#!/usr/bin/env bash
set -Eeuo pipefail

PHP_BIN="/usr/bin/php"
TASK="/home/01_html/08_server_update_paper_selection.php"
COOLDOWN="${COOLDOWN_SECONDS:-8}"

FLOCK="/usr/bin/flock"
LOCKDIR="/tmp/08_sups_locks"                # 可改成 /home/01_html/.locks
mkdir -p "$LOCKDIR"

SCHED_LOCK="$LOCKDIR/08_server_sups_scheduler.lock"
RUN_LOCK="$LOCKDIR/08_server_sups_worker.lock"

# 若已有调度器在跑，则本次立即退出（非阻塞）
exec 9>"$SCHED_LOCK"
if ! "$FLOCK" -n 9; then
  exit 0
fi

trap 'exit 0' INT TERM

while :; do
  "$FLOCK" -x "$RUN_LOCK" "$PHP_BIN" "$TASK" || true
  sleep "$COOLDOWN"
done
