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
        $graph1_percent = $active_users > 0 ? round(($users_logged_in_30days / $active_users) * 100, 2) : 0;

        // Graph 2: New users added
        $total_users = $DB->count_records('user', array('deleted' => 0));
        $new_users_added = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {user}
            WHERE firstaccess >= :firstaccess
        ", array('firstaccess' => time() - (30 * 24 * 60 * 60)));
        $graph2_percent = $total_users > 0 ? round(($new_users_added / $total_users) * 100, 2) : 0;

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
        $graph3_percent = $quiz_attempts > 0 ? round(($quiz_completions / $quiz_attempts) * 100, 2) : 0;

        // Graph 4: Course access
        $total_courses = $DB->count_records('course', array('visible' => 1));
        $courses_accessed = $DB->count_records_sql("
            SELECT COUNT(DISTINCT courseid)
            FROM {user_lastaccess}
            WHERE timeaccess >= :timeaccess
        ", array('timeaccess' => time() - (30 * 24 * 60 * 60)));
        $graph4_percent = $total_courses > 0 ? round(($courses_accessed / $total_courses) * 100, 2) : 0;

        $graph1_html = $this->get_graph_html($graph1_percent, 'graph1_title', 'graph1_description');
        $graph2_html = $this->get_graph_html($graph2_percent, 'graph2_title', 'graph2_description');
        $graph3_html = $this->get_graph_html($graph3_percent, 'graph3_title', 'graph3_description');
        $graph4_html = $this->get_graph_html($graph4_percent, 'graph4_title', 'graph4_description');

        $output = "
            <div class='progress-graphs'>
                $graph1_html
                $graph2_html
                $graph3_html
                $graph4_html
            </div>
        ";

        return $output;
    }

    private function get_graph_html($percent, $title_string, $description_string) {
        $title = get_string($title_string, 'block_myprogressgraphs');
        $description = get_string($description_string, 'block_myprogressgraphs');
        
        $graph_content = $percent > 0 ?
            "<div class='progress'>
                <div class='progress-bar' role='progressbar' style='width: {$percent}%; background-color: " . $this->get_progress_bar_color($percent) . ";' aria-valuenow='{$percent}' aria-valuemin='0' aria-valuemax='100'>{$percent}%</div>
            </div>" :
            "<p>No data available</p>";

        return "
            <div class='progress-graph'>
                <hr>
                <h6>{$title}</h6>
                <p class='graph-description'>{$description}</p>
                {$graph_content}
            </div>
        ";
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