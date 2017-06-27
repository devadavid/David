<?php // $Id$

defined('MOODLE_INTERNAL') || die();

class rb_source_count extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $sourcetitle;

    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        if ($groupid instanceof rb_global_restriction_set) {
            throw new coding_exception('Wrong parameter orders detected during report source instantiation.');
        }
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        // Apply global user restrictions.
        $this->add_global_report_restriction_join('base', 'userid', 'auser');

      //  start base query for count of users who had answered for question 2 in feedback ingegno 2-6-2017 
       
        $this->base = '(select id,username from {user})';
	   //$this->base = '{user}';
		//  end base query for count of users who had answered for question 2 in feedback ingegno 2-6-2017 
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_count');

        parent::__construct();
    }

    public function global_restrictions_supported() {
        return true;
    }

     protected function define_joinlist() {
        
        $joinlist = array(
          
			
				new rb_join(
                'joininprogress',
                'INNER',
                "(select IF(status=10,'1','0') as status1  , IF(status=25,'1','0') as status2, 
				IF(status=50,'1','0') as status3,co.fullname as course,userid,
				cc.timecompleted as coursecompleted ,cc.timeenrolled as timeenrolled
				from {course_completions} cc,
				{course} co where cc.course=co.id group by course,userid)",
				'base.id = joininprogress.userid'
				),
				new rb_join(
                'joinorg',
                'left',
                '(select uid.userid as userid,uid.data as data,cc.course as course from {user_info_data} uid,{course_completions} cc
				where fieldid=3 and uid.userid=cc.userid group by data,userid)',
				'base.id = joinorg.userid'
				),
					 new rb_join(
                'joingender',
                'left',
                "(SELECT uid.userid as userid,case when data='femelle'  then 'female' when data='Female'  then 'female'
                 when data='hembra'  then 'female' 
				when data='إناثا'  then 'female' when data='masculino'  then 'male' when data='Male'  then 'male' 
				when data='mâle'  then 'male' 
				when data='الذكر'  then 'male' when data='غير محدد'  then 'notspecified' when data='non précisé'  then 'notspecified'
				when data='Not Specified'  then 'notspecified' when data='no especificado'  then 'notspecified' else data  end data 
				FROM {user_info_data} uid,{course_completions} cc
				WHERE data LIKE '%femelle%' OR data LIKE '%hembra%' OR data LIKE '%إناثا%' OR data LIKE '%masculino%' OR 
				data LIKE '%mâle%' OR data LIKE '%الذكر%' OR data LIKE '%غير محدد%' OR data LIKE '%Male%' OR data LIKE '%Female%' OR data LIKE '%Not Specified%'
				OR data LIKE '%non précisé%' OR data LIKE '%no especificado%'and cc.userid=uid.userid group by userid,data
                    )",
				'base.id = joingender.userid'
				),
				 new rb_join(
                'joinage',
                'left',
                "(select uid.userid,uid.data 
					from {user_info_data} uid,{user_info_field} uif,{course_completions} cc
					where uid.fieldid=uif.id and uif.name='AGE' and uid.userid=cc.userid group by data
                    )",
				'base.id = joinage.userid'
				),
									
				
				
				
        );

        return $joinlist;
    }
	//  start column field added name,ans1, ans2 for feedback_question_2 ingegno 2-6-2017 

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array(
           
           // custom code of column for question 2 name
           
            new rb_column_option(
                'user',
                'countenrolled',
                get_string('countenrolled', 'rb_source_count'),
                'joininprogress.status1',
				 array(
					  'joins' => 'joininprogress',
                    'displayfunc' => 'yes_or_no',
                    'dbdatatype' => 'boolean',
                    
                )
			),
			 new rb_column_option(
                'user',
                'countinprogress',
                get_string('countinprogress', 'rb_source_count'),
                'joininprogress.status2',
				
					
					 array(
					  'joins' => 'joininprogress',
                    'displayfunc' => 'yes_or_no',
                    'dbdatatype' => 'boolean',
                    
                )
                
			),
			 new rb_column_option(
                'user',
                'countcompleted',
                get_string('countcompleted', 'rb_source_count'),
                'joininprogress.status3',
				 array(
					  'joins' => 'joininprogress',
                    'displayfunc' => 'yes_or_no',
                    'dbdatatype' => 'boolean',
                    
                )
			),
			new rb_column_option(
                'user',
                'coursename',
                get_string('coursename', 'rb_source_count'),
                'joininprogress.course',
				array(
        'joins' => 'joininprogress',
					)
			),
			new rb_column_option(
                'user',
                'coursecompleted',
                get_string('coursecompleted', 'rb_source_count'),
                'joininprogress.coursecompleted',
				 array(
					  'joins' => 'joininprogress',
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                    
                )
			),
			new rb_column_option(
                'user',
                'timeenrolled',
                get_string('timeenrolled', 'rb_source_count'),
                'joininprogress.timeenrolled',
				array(
					  'joins' => 'joininprogress',
                    'displayfunc' => 'nice_date',
                    'dbdatatype' => 'timestamp',
                    
                )
			),
			 new rb_column_option(
			'user',
			'username',
			get_string('users'),
			'base.username'
			),
			new rb_column_option(
                'user',
                'org',
                get_string('org', 'rb_source_count'),
                'joinorg.data',
				array(
        'joins' => 'joinorg',
		
			)
			),
			new rb_column_option(
                'user',
                'gender',
                get_string('gender', 'rb_source_count'),
                'joingender.data',
				array(
        'joins' => 'joingender',
		)
			),
			new rb_column_option(
                'user',
                'age',
                get_string('age', 'rb_source_count'),
                'joinage.data',
				array(
        'joins' => 'joinage',
		)
			),
	);
        return $columnoptions;
    }
	//  end column field added name,ans1, ans2 for feedback_question_2 ingegno 2-6-2017 
	//  start default column field added name,ans1, ans2 for feedback_question_2 ingegno 2-6-2017 
 protected function define_defaultcolumns() {
       
    }
	

	protected function define_filteroptions() {
       $filteroptions = array(
            
            new rb_filter_option(
                'user',
                'coursecompleted',
                get_string('coursecompleted', 'rb_source_count'),
                'date'
            ),
			 new rb_filter_option(
                'user',
                'timeenrolled',
                get_string('timeenrolled', 'rb_source_count'),
                'date'
            ),
	);
			return $filteroptions;
    }
//  end default column field added name,ans1, ans2 for feedback_question_2 ingegno 2-6-2017 

 protected function define_contentoptions() {
        $contentoptions = array(
          
        );

        return $contentoptions;
    }

}
