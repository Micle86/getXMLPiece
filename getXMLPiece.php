<?php
	
	namespace GoodWheels;
	/**
	*читает большой файл xml построчно и вытаскивает заданные клиентом узлы
	*
	*/
	class getXMLPiece{
		protected $metka;
		protected $is_enclosed_tag;
		public function __construct($file, $is_enclosed_tag = false)
		{
			//является ли имя файла ссылкой
			if( !filter_var( $file, FILTER_VALIDATE_URL ) ){
				if( !file_exists($file) ) throw new \Exception('Не найден файл '.$file);
				if( !is_readable($file) ) throw new \Exception('Файл  '.$file.' недоступен для чтения');
			}
			//10 попыток получить файл @ проставлена так как выкидывает предупреждение если не удалось с первого раза отрыть документ
			$count = 10;
			while( !($this->metka = @fopen($file, 'r') ) && $count > 0 )
			{
				sleep(1);
				--$count;
			}
			if( !$this->metka ) throw new \Exception('Не удалось открыть файл '.$file);
			$this->is_enclosed_tag = $is_enclosed_tag;
		}
		public function __destruct()
		{
			fclose($this->metka);
		}
		/**
		*выдает порциями XML
		*
		*@param array $tag ['tag_name' =>'','attr'=>[]]
		*/
		public function readByTeg(array $tag)
		{
			if(!isset($tag['tag_name'])) throw new \Exception('В передаваемом массиве не указан tag_name');
			while( $line = fgets($this->metka) )
			{
				if( $this->isTrueTag($tag, $line) ){
					if( stripos($line, '/>')!==false ) yield $line;
					else yield $this->getXML($tag, $line);
				} 
			}
		}
		/**
		*проверяет: содержит ли эта строка искомый узел
		*
		*@param array $tag ['tag_name' =>'','attr'=>[]]
		*@param string $line
		*/
		protected function isTrueTag($tag, $line)
		{
			$is_true = true; //удовлетворяет ли узел всем тербованиям?
			if(stripos($line, '<'.$tag['tag_name'])!==false){
				//в том случае если указаны аттрибуты узла - ищем их наличие и значение
				if( isset($tag['attr']) && sizeof($tag['attr'])){
					foreach( $tag['attr'] as $key => $value){
						if($value){
							if( stripos($line, $key.'="'.$value.'"' ) ===false ) $is_true = false;
						}
						else{
							if(stripos($line, $key )===false) $is_true = false;
						}
					}
				}
			}
			else $is_true = false;
			return $is_true;
		}
		/**
		*собирает XML из строк, пока не дойдет до закрывающего тега
		*
		*@param array $tag ['tag_name' =>'','attr'=>[]]
		*@param string $line
		*@return string
		*/
		protected function getXML($tag, $line)
		{
			$xml = $line;
			if ($this->is_enclosed_tag)
			{
				$lvl = 1; //уровень вложенности
				while( ($line = fgets($this->metka) ) && $lvl>0 )
				{
					$xml .= $line;
					if( stripos( $line, '</'.$tag['tag_name'].'>' )!==false){
						--$lvl;
					}
					if( stripos( $line, '<'.$tag['tag_name'] )!==false){
						++$lvl;
					}
					return $xml;
				}
			}
			else
			{
				while( $line = fgets($this->metka) )
				{
					$xml .= $line;
					if( stripos( $line, '</'.$tag['tag_name'].'>' )!==false){
						return $xml;
					}
				}
			}
			
			
		}
	}
	