читает большой файл xml построчно и вытаскивает заданные клиентом узлы
Использование:

//прочесть файл $file( может быть ссылкой )
$read_xml = new \GoodWheels\getXMLPiece($file);
// читать файл по тегу offer с аттрибутом type равному tyre из разряда (<offer type="tyre">...</offer>)
foreach( $read_xml->readByTeg(['tag_name' =>'offer','attr'=>['type'=>'tyre']]) as $value){
    	//устроняем проблемы кодировки/
    	$value = mb_convert_encoding($value, 'UTF-8', 'windows-1251');
    	$arr_tire = new SimpleXMLElement( $value ); //получаем объект из прочтенного куска xml
}
//если встречается конструкция, где в теге встречается повторяющийся тег, например <div>...<div>...</div></div>
//, то задача усложняется и перед чтением необходимо это указать второй параметр true $read_xml = new \GoodWheels\getXMLPiece( $file, true );