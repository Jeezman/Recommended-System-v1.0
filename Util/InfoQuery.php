<?php

class InfoQuery{


	private $conn;//���ݿ�
	
	function __construct(){
		//��ʼ�����ݿⲢ����
		$this->conn = new MySQL();
		$this->conn->connect();
	}
	function closeConn(){
		$this->conn->close();
	}
	
	
	
	function get_posting_list($lex_arr){
		$doc_id_list_arr = null;
		foreach($lex_arr as $lex){
			$lex_id["$lex"] = $this->get_lex_id($lex);//��ȡlexicon�ڱ��е�Id
			//���õ��ʲ��ڴʻ���У�����ѡ������
			if($lex_id["$lex"] == null){
// 				echo $lex."������";
 				continue;//ֱ����һ��ѭ��
			}
			//debug
// 			echo "<br/>";
// 			echo $lex."--id:";
// 			echo var_dump($lex_id["$lex"]);
			//debug
			
			$doc_id_list_arr["$lex"] = $this->get_doc_id_list($lex_id["$lex"]);//�ӵ��ű��л�ȡÿ���������ڵ�����
			//debug
// 			echo "<br/>";
// 			echo "doc id list arr:";
// 			var_dump($doc_id_list_arr["$lex"]);
			//debug
		
		}
// 		var_dump($doc_id_list_arr);
		return $doc_id_list_arr;
		
	}
	
	
	/**
	 * ��ȡLex��id,���򷵻�null
	 * @param String $lex
	 */
	function get_lex_id($lex){
		$sql = "SELECT `ID` FROM `lexicon` WHERE `LexContent` = '$lex'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? null : $row["ID"];
	}

	/**
	 * ��ȡLex��df,����0
	 * @param String $lex
	 */
	function get_lex_df($lex){
		$sql = "SELECT `DF` FROM `lexicon` WHERE `LexContent` = '$lex'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? 0 : $row["DF"];
	}
	/**
	 * ��ȡ��������
	 * @param String $lex
	 */
	function get_num_of_doc(){
		$sql = "SELECT COUNT(*) FROM `doc_collection`";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? null : $row[0];
	}
		
	/**
	 * �ӵ��ű��л�ȡ�õ��ʣ�id�����ڵ����£���������ʽ����
	 * @param String $lex
	 */
	function get_doc_id_list($lex_id){
		$sql = "SELECT `DocID` FROM `posting_list` WHERE `LexID` = '$lex_id'";
		$result = $this->conn->query($sql);
		$doc_id_list = "";
		while($row = $this->conn->fetch_array($result)){
			$doc_id_list .= $row["DocID"].",";
		}
		$doc_id_list = substr($doc_id_list, 0, strlen($doc_id_list) - 1);//ȥ�����һ������
		return  explode(",", $doc_id_list);//�ָ������

	}
	
	/**
	 * ��ȡ�������������ؼ��ֵ�����id��Ϊ��ȡ�ϣ���������ʽ���أ�
	 * ����ÿ�����ʵĵ��ű����������������ʵ�ժҪ�Ϳ�����Ϊ��ѡ���ɱ���������
	 * @param Array $doc_id_list_arr
	 */
	function get_doc_ids($doc_id_list_arr){
		if($doc_id_list_arr == null){
			return null;
		}
		
		//�󽻼�����ȡ��
		$length = sizeof($doc_id_list_arr);//���鳤��
		$result = array();//�����������������ʵĵ��ű�Ľ���
		$doc_id_list_arr_v = array_values($doc_id_list_arr);
		for($i = 0; $i < $length; $i++){
			for($j = $i + 1; $j < $length; $j++){
				$tmp1 = array_intersect($doc_id_list_arr_v[$i], $doc_id_list_arr_v[$j]);
				for($k = $j + 1; $k < $length; $k++){
					$tmp2 = array_intersect($tmp1, $doc_id_list_arr_v[$k]);
					for($m = $k + 1; $m < $length; $m++){
						$tmp3 = array_intersect($tmp2, $doc_id_list_arr_v[$m]);
						$result = array_values(array_unique(array_merge($result,$tmp3)));
					}
				}	
			}
		}
		
		
		
		//�󽻼�����ȡ��
// 		$curr = current($doc_id_list_arr);
// 		$result = array();//�������
// 		while($next = next($doc_id_list_arr)){
// 			$tmp = array_intersect($curr, $next);//�󽻼�
// 			$result = array_merge($result,$tmp);
// 		}
		return array_unique($result);
		
	}
	
	/**
	 * ��ȡ���µ���Ϣ�������ʽ����
	 * @param Array $doc_ids
	 */
	function get_doc_info_json($doc_ids){
		if($doc_ids == null){
			return "";
		}
		$res = array();
		//��ȡ����Ӧ������
		foreach($doc_ids as $doc_id){
			$sql = "SELECT * FROM `doc_collection` WHERE `ID`='$doc_id'";
			
			$result = $this->conn->query($sql);

			$row = $this->conn->fetch_array($result);
			
			array_push($res, $row);
		}
		return $res;
	}
	
	/**
	 * ��ȡһƪ���µ�ժҪ
	 * @param unknown $doc_id_arr
	 */
	function getAbs($doc_id){
		$sql = "SELECT `Abstract` FROM `doc_collection` WHERE `ID`='$doc_id'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row["Abstract"];
	}

	/**
	 * ��ȡ�������������id
	 * @param unknown $cid
	 */
	function getCid($docid){
		$sql = "SELECT `ConferenceID` FROM `doc_collection` WHERE `ID`='$docid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row["ConferenceID"];
	}
	
	/**
	 * ��ȡ��ػ����е�������
	 * @param unknown $cid ����Id
	 * @return unknown
	 */
	function getPaperNum($cid){
		$sql = "SELECT count(*) FROM `doc_collection` WHERE `ConferenceID`='$cid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];
	}
	
	/**
	 * ��ȡ����id->Name
	 * @param unknown $cid
	 * @return Ambigous <>
	 */
	function getConfName($cid){
		$sql = "SELECT `name` FROM `conference_journal` WHERE `ID`='$cid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];		
	}
	
	
	/*********************************************���Բ���*****************************/
	
	
	
	/**
	 * ��ȡ���в�������ժҪ
	 */
	function getTestAbs(){
		$sql = "SELECT `ID`,`Abstract` FROM `test`";
		$result = $this->conn->query($sql);
		return $result;
	}
	
	/**
	 * ���²���������Ԥ�����ID
	 * @param unknown $test_paper_ID
	 * @param unknown $predictConfID
	 */
	function UpdateConfPreID($test_paper_ID, $predictConfID){
		$sql = "UPDATE `test` SET `Predict` = '$predictConfID' WHERE `ID` = '$test_paper_ID'";
		$this->conn->query($sql);
	}
	
	/**
	 * ������ȷ��������
	 * @return unknown
	 */
	function getCorrectNum($i){
		$sql = "SELECT `ConferenceID`, `Predict` FROM `test`";
		$result = $this->conn->query($sql);
		$k = 0;//����
		while($row = $this->conn->fetch_array($result)){
			if( in_array($row["ConferenceID"], array_slice(explode(",", $row["Predict"]),0,$i) ) ){
				$k++;
			}
		}
		return $k;
	}
	/**
	 * �������ɹ���������
	 * @return unknown
	 */
	function getRetrievaledNum(){
		$sql = "SELECT count(*) FROM `test` WHERE `Predict` != '-1' && `Predict` != '-2' && `Predict` != '0' && `Predict` != ''";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];		
	}
	/**
	 * ������������
	 * @return unknown
	 */
	function getTotalTestCaseNum(){
		$sql = "SELECT count(*) FROM `test`";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];
	}
	
	/**
	 * ��Ԥ��ֵ��ΪĬ��ֵ-2
	 */
	function setPredictToDefault(){
		$sql = "UPDATE `test` SET `Predict` = -2";
		$this->conn->query($sql);
	}
}

?>