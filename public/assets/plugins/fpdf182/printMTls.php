<?php
require('fpdf.php');

class FPDF_CellFit extends FPDF
{
    //Cell with horizontal scaling if text is too wide
    function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $scale=false, $force=true)
    {
        //Get string width
        $str_width=$this->GetStringWidth($txt);

        //Calculate ratio to fit cell
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $ratio = ($w-$this->cMargin*2)/$str_width;

        $fit = ($ratio < 1 || ($ratio > 1 && $force));
        if ($fit)
        {
            if ($scale)
            {
                //Calculate horizontal scaling
                $horiz_scale=$ratio*100.0;
                //Set horizontal scaling
                $this->_out(sprintf('BT %.2F Tz ET',$horiz_scale));
            }
            else
            {
                //Calculate character spacing in points
                $char_space=($w-$this->cMargin*2-$str_width)/max(strlen($txt)-1,1)*$this->k;
                //Set character spacing
                $this->_out(sprintf('BT %.2F Tc ET',$char_space));
            }
            //Override user alignment (since text will fill up cell)
            $align='';
        }

        //Pass on to Cell method
        $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);

        //Reset character spacing/horizontal scaling
        if ($fit)
            $this->_out('BT '.($scale ? '100 Tz' : '0 Tc').' ET');
    }

    //Cell with horizontal scaling only if necessary
    function CellFitScale($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,false);
    }

    //Cell with horizontal scaling always
    function CellFitScaleForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,true);
    }

    //Cell with character spacing only if necessary
    function CellFitSpace($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,false);
    }

    //Cell with character spacing always
    function CellFitSpaceForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        //Same as calling CellFit directly
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,true);
    }

    // Page header
        function Header()
        {
            // Logo
            $this->Image('../../../public/assets/images/logo.jpg',10,10,50);
            // Arial bold 15
            // $this->SetFont('Arial','B',15);
            $this->SetFont('Arial','I',8);
            // Move to the right
            // $this->Cell(80);
            // Title
            // $this->Cell(30,10,'Title',1,0,'C');
            $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'R');
            $this->Ln(4);
            $this->Cell(0,10, 'Date Generated: '.date('F j, Y H:i:s A'),0,0,'R');
            // Line break
            $this->Line(10, 41, 279-10, 42);
            $this->Ln(30);
            $this->setFont('Arial', 'B', 14);
            $this->Cell(0,10, 'Materials with Qty < Required Qty Report',0,1, 'C');
        }

        // Page footer
        function Footer()
        {
            // Position at 1.5 cm from bottom
            $this->SetY(-23);
            // Arial italic 8
            $this->SetFont('Arial','I',8);
            // Page number
            // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
            $this->Image('../../../public/assets/images/under.jpg', 210, $this->GetY(), 60);
            // $this->Image('../../../public/assets/images/logo.jpg',10,10,50);
        }

        // Colored table
        function TableHeader($header){
            // Colors, line width and bold font
            $this->SetFillColor(152, 152, 152);
            $this->SetTextColor(255);
            $this->SetDrawColor(0);
            $this->SetLineWidth(.1);
            $this->SetFont('','B');
            // Header
            $w = array(33, 70, 13, 38, 25, 17, 38, 25);
            for($i=0;$i<count($header);$i++)
                $this->CellFitScale($w[$i],10,$header[$i],1,0,'C',true);
            $this->Ln();
        }

        function TableBody($row)
        {   
            $this->SetFillColor(234, 153, 153);
            $this->SetTextColor(0);
            $this->SetDrawColor(0);
            $this->SetLineWidth(.2);
            $this->SetFont('');
            // Data
            // $fill = false;
            $w = array(33, 70, 13, 38, 25, 17, 38, 25);
            $fill = false;
            $counter = 0;
            
                        if($row['MATERIALS'] != null || $row['MATERIALS'] != '')
                        {
                            $this->CellFitScale($w[0],8,$row['MATERIALS'],1,0,'L',$fill);
                            // $this->CellFitScale(40,7,$col,1);
                        }
                        else{
                            $this->CellFitScale($w[0],8,'---',1,0,'L',$fill);
                        }
                        
                        if($row['DESCRIPTION'] != null || $row['DESCRIPTION'] != '')
                        {
                            $this->CellFitScale($w[1],8,$row['DESCRIPTION'],1,0,'L',$fill);
                        }
                        else{
                            $this->CellFitScale($w[1],8,'---',1,0,'L',$fill);
                        }

                        if($row['UOM'] != null || $row['UOM'] != '')
                        {
                            $this->CellFitScale($w[2],8,$row['UOM'],1,0,'L',$fill);
                        }
                        else{
                            $this->CellFitScale($w[2],8,'---',1,0,'L',$fill);
                        }

                        if($row['COMP_QTY'] != null || $row['COMP_QTY'] != '')
                        {
                            if (strpos($row['COMP_QTY'], ".") !== false) {
                                $this->CellFitScale($w[3],8,number_format($row['COMP_QTY'], strlen(explode(".", $row['COMP_QTY'])[1])),1,0,'R',$fill);
                            }
                            else{
                                $this->CellFitScale($w[3],8,number_format($row['COMP_QTY']),1,0,'R',$fill);
                            }
                        }
                        else{
                            $this->CellFitScale($w[3],8,'0',1,0,'R',$fill);
                        }

                        if($row['QTY_ALLOCATION'] != null || $row['QTY_ALLOCATION'] != '')
                        {
                            if (strpos($row['QTY_ALLOCATION'], ".") !== false) {
                                $this->CellFitScale($w[4],8,number_format($row['QTY_ALLOCATION'], strlen(explode(".", $row['QTY_ALLOCATION'])[1])),1,0,'R',$fill);
                            }
                            else{
                                $this->CellFitScale($w[4],8,number_format($row['QTY_ALLOCATION']),1,0,'R',$fill);
                            }
                        }
                        else{
                            $this->CellFitScale($w[4],8,'0',1,0,'R',$fill);
                        }

                        if($row['RAW_EOH'] != null || $row['RAW_EOH'] != '')
                        {
                            if (strpos($row['RAW_EOH'], ".") !== false) {
                                $this->CellFitScale($w[5],8,number_format($row['RAW_EOH'], strlen(explode(".", $row['RAW_EOH'])[1])),1,0,'R',$fill);
                            }
                            else{
                                $this->CellFitScale($w[5],8,number_format($row['RAW_EOH']),1,0,'R',$fill);
                            }
                        }
                        else{
                            $this->CellFitScale($w[5],8,'0',1,0,'R',$fill);
                        }

                        if($row['UNCOMMITTED_EOH_UR'] != null || $row['UNCOMMITTED_EOH_UR'] != '')
                        {
                            if (strpos($row['UNCOMMITTED_EOH_UR'], ".") !== false) {
                                $this->CellFitScale($w[6],8,number_format($row['UNCOMMITTED_EOH_UR'], strlen(explode(".", $row['UNCOMMITTED_EOH_UR'])[1])),1,0,'R',$fill);
                            }
                            else{
                                $this->CellFitScale($w[6],8,number_format($row['UNCOMMITTED_EOH_UR']),1,0,'R',$fill);
                            }
                        }
                        else{
                            $this->CellFitScale($w[6],8,'0',1,0,'R',$fill);
                        }

                        if($row['QTY_UR'] != null || $row['QTY_UR'] != '')
                        {
                            if (strpos($row['QTY_UR'], ".") !== false) {
                                $this->CellFitScale($w[7],8,number_format($row['QTY_UR'], strlen(explode(".", $row['QTY_UR'])[1])),1,0,'R',$fill);
                            }
                            else{
                                $this->CellFitScale($w[7],8,number_format($row['QTY_UR']),1,0,'R',$fill);
                            }
                        }
                        else{
                            $this->CellFitScale($w[7],8,'0',1,0,'R',$fill);
                        }
                        
                        $this->Ln();
                    
                }
                
            // Closing line
            // $this->Cell(array_sum($w),0,'','T');
        

        var $B=0;
        var $I=0;
        var $U=0;
        var $HREF='';
        var $ALIGN='';

        function WriteHTML($html)
        {
            //HTML parser
            $html=str_replace("\n",' ',$html);
            $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
            foreach($a as $i=>$e)
            {
                if($i%2==0)
                {
                    //Text
                    if($this->HREF)
                        $this->PutLink($this->HREF,$e);
                    elseif($this->ALIGN=='center')
                        $this->Cell(0,5,$e,0,1,'C');
                    else
                        $this->Write(5,$e);
                }
                else
                {
                    //Tag
                    if($e[0]=='/')
                        $this->CloseTag(strtoupper(substr($e,1)));
                    else
                    {
                        //Extract properties
                        $a2=explode(' ',$e);
                        $tag=strtoupper(array_shift($a2));
                        $prop=array();
                        foreach($a2 as $v)
                        {
                            if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                                $prop[strtoupper($a3[1])]=$a3[2];
                        }
                        $this->OpenTag($tag,$prop);
                    }
                }
            }
        }

        function OpenTag($tag,$prop)
        {
            //Opening tag
            if($tag=='B' || $tag=='I' || $tag=='U')
                $this->SetStyle($tag,true);
            if($tag=='A')
                $this->HREF=$prop['HREF'];
            if($tag=='BR')
                $this->Ln(5);
            if($tag=='P')
                $this->ALIGN=$prop['ALIGN'];
            if($tag=='HR')
            {
                if( !empty($prop['WIDTH']) )
                    $Width = $prop['WIDTH'];
                else
                    $Width = $this->w - $this->lMargin-$this->rMargin;
                $this->Ln(2);
                $x = $this->GetX();
                $y = $this->GetY();
                $this->SetLineWidth(0.4);
                $this->Line($x,$y,$x+$Width,$y);
                $this->SetLineWidth(0.2);
                $this->Ln(2);
            }
        }

        function CloseTag($tag)
        {
            //Closing tag
            if($tag=='B' || $tag=='I' || $tag=='U')
                $this->SetStyle($tag,false);
            if($tag=='A')
                $this->HREF='';
            if($tag=='P')
                $this->ALIGN='';
        }

        function SetStyle($tag,$enable)
        {
            //Modify style and select corresponding font
            $this->$tag+=($enable ? 1 : -1);
            $style='';
            foreach(array('B','I','U') as $s)
                if($this->$s>0)
                    $style.=$s;
            $this->SetFont('',$style);
        }

        function PutLink($URL,$txt)
        {
            //Put a hyperlink
            $this->SetTextColor(0,0,255);
            $this->SetStyle('U',true);
            $this->Write(5,$txt,$URL);
            $this->SetStyle('U',false);
            $this->SetTextColor(0);
        }

}
?>