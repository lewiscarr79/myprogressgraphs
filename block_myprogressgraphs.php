<?php
class block_myprogressgraphs extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_myprogressgraphs');
    }

    public function instance_allow_config() {
        return true;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = $this->display_graphs();
        $this->content->footer = '';

        return $this->content;
    }

    private function display_graphs() {
        global $DB;

        // Graph 1: Users logged in within the last 30 days
        $active_users = $DB->count_records('user', array('deleted' => 0, 'suspended' => 0));
        $users_logged_in_30days = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {user_lastaccess}
            WHERE timeaccess >= :timeaccess
        ", array('timeaccess' => time() - (30 * 24 * 60 * 60)));
        $graph1_percent = round(($users_logged_in_30days / $active_users) * 100, 2);

        // Graph 2: New users added
        $total_users = $DB->count_records('user', array('deleted' => 0));
        $new_users_added = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {user}
            WHERE firstaccess >= :firstaccess
        ", array('firstaccess' => time() - (30 * 24 * 60 * 60)));
        $graph2_percent = round(($new_users_added / $total_users) * 100, 2);

        // Graph 3: Quiz completions vs attempts
        $quiz_completions = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {quiz_attempts}
            WHERE state = 'finished'
            AND timefinish >= :timefinish
        ", array('timefinish' => time() - (30 * 24 * 60 * 60)));
        $quiz_attempts = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {quiz_attempts}
            WHERE timefinish >= :timefinish
        ", array('timefinish' => time() - (30 * 24 * 60 * 60)));
        $graph3_percent = round(($quiz_completions / $quiz_attempts) * 100, 2);

        // Graph 4: Course access
        $total_courses = $DB->count_records('course', array('visible' => 1));
        $courses_accessed = $DB->count_records_sql("
            SELECT COUNT(DISTINCT courseid)
            FROM {user_lastaccess}
            WHERE timeaccess >= :timeaccess
        ", array('timeaccess' => time() - (30 * 24 * 60 * 60)));
        $graph4_percent = round(($courses_accessed / $total_courses) * 100, 2);

        $output = "
            <div class='progress-graphs'>
                <div class='progress-graph'>
                <hr>
                    <h6>" . get_string('graph1_title', 'block_myprogressgraphs') . "</h6>
                    <p class='graph-description'>" . get_string('graph1_description', 'block_myprogressgraphs') . "</p>
                    <div class='progress'>
                        <div class='progress-bar' role='progressbar' style='width: {$graph1_percent}%; background-color: " . $this->get_progress_bar_color($graph1_percent) . ";' aria-valuenow='{$graph1_percent}' aria-valuemin='0' aria-valuemax='100'>{$graph1_percent}%</div>
                    </div>
                </div>
                <div class='progress-graph'>
                 <hr>
                    <h6>" . get_string('graph2_title', 'block_myprogressgraphs') . "</h6>
                    <p class='graph-description'>" . get_string('graph2_description', 'block_myprogressgraphs') . "</p>
                    <div class='progress'>
                        <div class='progress-bar' role='progressbar' style='width: {$graph2_percent}%; background-color: " . $this->get_progress_bar_color($graph2_percent) . ";' aria-valuenow='{$graph2_percent}' aria-valuemin='0' aria-valuemax='100'>{$graph2_percent}%</div>
                    </div>
                </div>
                <div class='progress-graph'>
                 <hr>
                    <h6>" . get_string('graph3_title', 'block_myprogressgraphs') . "</h6>
                    <p class='graph-description'>" . get_string('graph3_description', 'block_myprogressgraphs') . "</p>
                    <div class='progress'>
                        <div class='progress-bar' role='progressbar' style='width: {$graph3_percent}%; background-color: " . $this->get_progress_bar_color($graph3_percent) . ";' aria-valuenow='{$graph3_percent}' aria-valuemin='0' aria-valuemax='100'>{$graph3_percent}%</div>
                    </div>
                </div>
                <div class='progress-graph'>
                 <hr>
                    <h6>" . get_string('graph4_title', 'block_myprogressgraphs') . "</h6>
                    <p class='graph-description'>" . get_string('graph4_description', 'block_myprogressgraphs') . "</p>
                    <div class='progress'>
                        <div class='progress-bar' role='progressbar' style='width: {$graph4_percent}%; background-color: " . $this->get_progress_bar_color($graph4_percent) . ";' aria-valuenow='{$graph4_percent}' aria-valuemin='0' aria-valuemax='100'>{$graph4_percent}%</div>
                    </div>
                </div>
            </div>
        ";

        return $output;
    }

    private function get_progress_bar_color($percent) {
        if ($percent >= 70) {
            return 'green';
        } elseif ($percent >= 40) {
            return 'orange';
        } else {
            return 'red';
        }
    }
}
?>