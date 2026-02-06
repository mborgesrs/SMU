<?php
/**
 * Minimal FPDF Library for PDF Generation
 * Based on FPDF 1.85 - Simplified version
 */

class FPDF {
    protected $page;
    protected $n;
    protected $offsets;
    protected $buffer;
    protected $pages;
    protected $state;
    protected $compress;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $StdPageSizes;
    protected $DefPageSize;
    protected $CurPageSize;
    protected $PageSizes;
    protected $wPt, $hPt;
    protected $w, $h;
    protected $lMargin;
    protected $tMargin;
    protected $rMargin;
    protected $bMargin;
    protected $cMargin;
    protected $x, $y;
    protected $lasth;
    protected $LineWidth;
    protected $fontpath;
    protected $CoreFonts;
    protected $fonts;
    protected $FontFiles;
    protected $diffs;
    protected $FontFamily;
    protected $FontStyle;
    protected $underline;
    protected $CurrentFont;
    protected $FontSizePt;
    protected $FontSize;
    protected $DrawColor;
    protected $FillColor;
    protected $TextColor;
    protected $ColorFlag;
    protected $ws;
    protected $images;
    protected $PageLinks;
    protected $links;
    protected $AutoPageBreak;
    protected $PageBreakTrigger;
    protected $InHeader;
    protected $InFooter;
    protected $ZoomMode;
    protected $LayoutMode;
    protected $title;
    protected $subject;
    protected $author;
    protected $keywords;
    protected $creator;
    protected $AliasNbPages;
    protected $PDFVersion;

    function __construct($orientation='P', $unit='mm', $size='A4') {
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageSizes = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        
        $this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
            'letter'=>array(612,792), 'legal'=>array(612,1008));
        
        if($unit=='pt')
            $this->k = 1;
        elseif($unit=='mm')
            $this->k = 72/25.4;
        elseif($unit=='cm')
            $this->k = 72/2.54;
        elseif($unit=='in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: '.$unit);
        
        if(is_string($size)) {
            $size = strtolower($size);
            if(!isset($this->StdPageSizes[$size]))
                $this->Error('Unknown page size: '.$size);
            $a = $this->StdPageSizes[$size];
            $this->DefPageSize = array($a[0]/$this->k, $a[1]/$this->k);
        } else {
            $this->DefPageSize = array($size[0]*$this->k, $size[1]*$this->k);
        }
        
        $this->CurPageSize = $this->DefPageSize;
        $this->DefOrientation = $orientation;
        $this->CurOrientation = $orientation;
        $this->wPt = $this->DefPageSize[0];
        $this->hPt = $this->DefPageSize[1];
        $this->w = $this->wPt/$this->k;
        $this->h = $this->hPt/$this->k;
        
        $margin = 28.35/$this->k;
        $this->SetMargins($margin,$margin);
        $this->cMargin = $margin/10;
        $this->LineWidth = .567/$this->k;
        $this->SetAutoPageBreak(true,2*$margin);
        $this->SetDisplayMode('default');
        $this->SetCompression(true);
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right=null) {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }

    function SetLeftMargin($margin) {
        $this->lMargin = $margin;
        if($this->page>0 && $this->x<$margin)
            $this->x = $margin;
    }

    function SetTopMargin($margin) {
        $this->tMargin = $margin;
    }

    function SetRightMargin($margin) {
        $this->rMargin = $margin;
    }

    function SetAutoPageBreak($auto, $margin=0) {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h-$margin;
    }

    function SetDisplayMode($zoom, $layout='default') {
        if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: '.$zoom);
        if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
            $this->LayoutMode = $layout;
        else
            $this->Error('Incorrect layout display mode: '.$layout);
    }

    function SetCompression($compress) {
        $this->compress = $compress;
    }

    function AliasNbPages($alias='{nb}') {
        $this->AliasNbPages = $alias;
    }

    function PageNo() {
        return $this->page;
    }

    function SetY($y) {
        if($y>=0)
            $this->y = $y;
        else
            $this->y = $this->h+$y;
    }

    function SetX($x) {
        if($x>=0)
            $this->x = $x;
        else
            $this->x = $this->w+$x;
    }

    function GetX() {
        return $this->x;
    }

    function GetY() {
        return $this->y;
    }


    function Line($x1, $y1, $x2, $y2) {
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }

    function SetDrawColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->DrawColor = sprintf('%.3F G',$r/255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
        if($this->page>0)
            $this->_out($this->DrawColor);
    }

    function SetTextColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g',$r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->TextColor!=$this->FillColor);
    }


    function AddPage($orientation='', $size='') {
        if($this->state==3)
            $this->Error('The document is closed');
        if($this->state==2) {
            // Page footer
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            // Close page
            $this->_out('Q');
        }
        $this->_beginpage($orientation,$size);
        // Page header
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
        $this->_out('2 J');
        $this->LineWidth = .567/$this->k;
        $this->_out(sprintf('%.2F w',$this->LineWidth*$this->k));
        if($this->FontFamily)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
        $this->DrawColor = '0 G';
        $this->_out($this->DrawColor);
        $this->FillColor = '0 g';
        $this->_out($this->FillColor);
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->_out('q');
    }

    function Header() {}
    function Footer() {}


    function SetFont($family, $style='', $size=0) {
        if($family=='')
            $family = $this->FontFamily;
        else
            $family = strtolower($family);
        $style = strtoupper($style);
        if(strpos($style,'U')!==false) {
            $this->underline = true;
            $style = str_replace('U','',$style);
        } else
            $this->underline = false;
        if($style=='IB')
            $style = 'BI';
        if($size==0)
            $size = $this->FontSizePt;
        
        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
            return;
        
        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey])) {
            if($family=='arial')
                $family = 'helvetica';
            if(!in_array($family, array('courier','helvetica','times','symbol','zapfdingbats')))
                $this->Error('Undefined font: '.$family.' '.$style);
            $this->fonts[$fontkey] = array('i'=>count($this->fonts)+1,'type'=>'core','name'=>$this->_getcorefontname($family,$style),'up'=>-100,'ut'=>50,'cw'=>array());
        }
        
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            $x = $this->x;
            $ws = $this->ws;
            if($ws>0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation,$this->CurPageSize);
            $this->x = $x;
            if($ws>0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw',$ws*$k));
            }
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $s = '';
        if($fill || $border==1) {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        if(is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'T')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
            if(strpos($border,'R')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'B')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        }
        if($txt!=='') {
            if($align=='R')
                $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')
                $dx = ($w-$this->GetStringWidth($txt))/2;
            else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $txt2 = str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
            if($this->underline)
                $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
            if($this->ColorFlag)
                $s .= ' Q';
            if($link)
                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0) {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $b = 0;
        if($border) {
            if($border==1) {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if(strpos($border,'L')!==false)
                    $b2 .= 'L';
                if(strpos($border,'R')!==false)
                    $b2 .= 'R';
                $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                if($this->ws>0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += isset($cw[$c]) ? $cw[$c] : 500;
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                    if($this->ws>0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                } else {
                    if($align=='J') {
                        $this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                    }
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            } else
                $i++;
        }
        if($this->ws>0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if($border && strpos($border,'B')!==false)
            $b .= 'B';
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $link='') {
        $cw = &$this->CurrentFont['cw'];
        $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1) {
                    $this->x = $this->lMargin;
                    $w = $this->w-$this->rMargin-$this->x;
                    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += isset($cw[$c]) ? $cw[$c] : 500;
            if($l>$wmax) {
                if($sep==-1) {
                    if($this->x>$this->lMargin) {
                        $this->x = $this->lMargin;
                        $this->y += $h;
                        $w = $this->w-$this->rMargin-$this->x;
                        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                        $i++;
                        $nl++;
                        continue;
                    }
                    if($i==$j)
                        $i++;
                    $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
                } else {
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1) {
                    $this->x = $this->lMargin;
                    $w = $this->w-$this->rMargin-$this->x;
                    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
            } else
                $i++;
        }
        if($i!=$j)
            $this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',false,$link);
    }

    function Ln($h=null) {
        $this->x = $this->lMargin;
        if($h===null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    function GetStringWidth($s) {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += isset($cw[$s[$i]]) ? $cw[$s[$i]] : 500;
        return $w*$this->FontSize/1000;
    }

    function SetFillColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g',$r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
        if($this->page>0)
            $this->_out($this->FillColor);
    }

    function Output($dest='', $name='', $isUTF8=false) {
        if($this->state<3)
            $this->Close();
        $dest = strtoupper($dest);
        if($dest=='') {
            if($name=='') {
                $name = 'doc.pdf';
                $dest = 'I';
            } else
                $dest = 'F';
        }
        switch($dest) {
            case 'I':
                $this->_checkoutput();
                if(PHP_SAPI!='cli') {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="'.$name.'"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo $this->buffer;
                break;
            case 'D':
                $this->_checkoutput();
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$name.'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                $f = fopen($name,'wb');
                if(!$f)
                    $this->Error('Unable to create output file: '.$name);
                fwrite($f,$this->buffer,strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: '.$dest);
        }
        return '';
    }

    protected function _checkoutput() {
        if(PHP_SAPI!='cli') {
            if(headers_sent($file,$line))
                $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
        }
        if(ob_get_length()) {
            if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents())) {
                ob_end_clean();
            } else {
                $this->Error("Some data has already been output, can't send PDF file");
            }
        }
    }

    protected function _getcorefontname($family,$style) {
        $name = ucfirst($family);
        if($family=='times' || $family=='helvetica')
            $name .= ($style=='B' || $style=='BI') ? '-Bold' : '';
        if($style=='I' || $style=='BI')
            $name .= ($family=='times') ? '-Italic' : '-Oblique';
        return $name;
    }

    protected function _beginpage($orientation,$size) {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        if(!$orientation)
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if(!$size)
            $size = $this->DefPageSize;
        else {
            if(is_string($size)) {
                $size = strtolower($size);
                if(!isset($this->StdPageSizes[$size]))
                    $this->Error('Unknown page size: '.$size);
                $a = $this->StdPageSizes[$size];
                $size = array($a[0]/$this->k, $a[1]/$this->k);
            }
        }
        if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1]) {
            if($orientation=='P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w*$this->k;
            $this->hPt = $this->h*$this->k;
            $this->PageBreakTrigger = $this->h-$this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
            $this->PageSizes[$this->page] = array($this->wPt, $this->hPt);
    }

    protected function _dounderline($x, $y, $txt) {
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
        return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
    }

    protected function _out($s) {
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }

    protected function _putpages() {
        $nb = $this->page;
        if(!empty($this->AliasNbPages)) {
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n] = str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
        }
        if($this->DefOrientation=='P') {
            $wPt = $this->DefPageSize[0]*$this->k;
            $hPt = $this->DefPageSize[1]*$this->k;
        } else {
            $wPt = $this->DefPageSize[1]*$this->k;
            $hPt = $this->DefPageSize[0]*$this->k;
        }
        for($n=1;$n<=$nb;$n++) {
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            if(isset($this->PageSizes[$n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
            $this->_out('/Resources 2 0 R');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            $p = $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.($this->compress ? '/Filter /FlateDecode ' : '').'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        $this->offsets[1] = strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for($i=0;$i<$nb;$i++)
            $kids .= (3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }

    protected function _putfonts() {
        foreach($this->fonts as $k=>$font) {
            $this->fonts[$k]['n'] = $this->n+1;
            $this->_newobj();
            $this->_out('<</Type /Font');
            $this->_out('/BaseFont /'.$font['name']);
            $this->_out('/Subtype /Type1');
            if($font['name']!='Symbol' && $font['name']!='ZapfDingbats')
                $this->_out('/Encoding /WinAnsiEncoding');
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    protected function _putresources() {
        $this->_putfonts();
        $this->offsets[2] = strlen($this->buffer);
        $this->_out('2 0 obj');
        $this->_out('<<');
        $this->_putresourcedict();
        $this->_out('>>');
        $this->_out('endobj');
    }

    protected function _putresourcedict() {
        $this->_out('/ProcSet [/PDF /Text]');
        $this->_out('/Font <<');
        foreach($this->fonts as $font)
            $this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
        $this->_out('>>');
    }

    protected function _putinfo() {
        $this->_out('/Producer '.$this->_textstring('FPDF '.FPDF_VERSION));
        if(!empty($this->title))
            $this->_out('/Title '.$this->_textstring($this->title));
        if(!empty($this->subject))
            $this->_out('/Subject '.$this->_textstring($this->subject));
        if(!empty($this->author))
            $this->_out('/Author '.$this->_textstring($this->author));
        if(!empty($this->keywords))
            $this->_out('/Keywords '.$this->_textstring($this->keywords));
        if(!empty($this->creator))
            $this->_out('/Creator '.$this->_textstring($this->creator));
        $this->_out('/CreationDate '.$this->_textstring('D:'.@date('YmdHis')));
    }

    protected function _putcatalog() {
        $this->_out('/Type /Catalog');
        $this->_out('/Pages 1 0 R');
        if($this->ZoomMode=='fullpage')
            $this->_out('/OpenAction [3 0 R /Fit]');
        elseif($this->ZoomMode=='fullwidth')
            $this->_out('/OpenAction [3 0 R /FitH null]');
        elseif($this->ZoomMode=='real')
            $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
        elseif(!is_string($this->ZoomMode))
            $this->_out('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
        if($this->LayoutMode=='single')
            $this->_out('/PageLayout /SinglePage');
        elseif($this->LayoutMode=='continuous')
            $this->_out('/PageLayout /OneColumn');
        elseif($this->LayoutMode=='two')
            $this->_out('/PageLayout /TwoColumnLeft');
    }

    protected function _putheader() {
        $this->_out('%PDF-'.$this->PDFVersion);
    }

    protected function _puttrailer() {
        $this->_out('/Size '.($this->n+1));
        $this->_out('/Root '.$this->n.' 0 R');
        $this->_out('/Info '.($this->n-1).' 0 R');
    }

    protected function _enddoc() {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        $this->_newobj();
        $this->_out('<<');
        $this->_putinfo();
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_putcatalog();
        $this->_out('>>');
        $this->_out('endobj');
        $o = strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 '.($this->n+1));
        $this->_out('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++)
            $this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
        $this->_out('trailer');
        $this->_out('<<');
        $this->_puttrailer();
        $this->_out('>>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
        $this->state = 3;
    }

    protected function _newobj() {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n.' 0 obj');
    }

    protected function _putstream($s) {
        $this->_out('stream');
        $this->_out($s);
        $this->_out('endstream');
    }

    protected function _textstring($s) {
        return '('.$this->_escape($s).')';
    }

    protected function _escape($s) {
        $s = str_replace('\\','\\\\',$s);
        $s = str_replace('(','\\(',$s);
        $s = str_replace(')','\\)',$s);
        $s = str_replace("\r",'\\r',$s);
        return $s;
    }

    function Close() {
        if($this->state==3)
            return;
        if($this->page==0)
            $this->AddPage();
        // Terminar página
        if($this->state==2) {
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            $this->_out('Q');
            $this->state = 1;
        }
        $this->_enddoc();
    }

    function AcceptPageBreak() {
        return $this->AutoPageBreak;
    }

    function Link($x, $y, $w, $h, $link) {
        // Simplified - not implemented
    }

    function Error($msg) {
        throw new Exception('FPDF error: '.$msg);
    }
}

define('FPDF_VERSION','1.85');
