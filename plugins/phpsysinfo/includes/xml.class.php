<?php
/* $Id: xml.class.php,v 1.1 2007/12/10 18:23:22 gpowers Exp $
*
* Copyright (c) 2006, 2007 by phpSysInfo
* http://phpsysinfo.sourceforge.net/
*
* This program is free software; you can redistribute it
* and/or modify it under the terms of the
* GNU General Public License version 2 (GPLv2)
* as published by the Free Software Foundation.
* See COPYING for details.
*
*/
class xml {
	private $sysinfo;
	private $mbinfo;
	private $hddtemp;
	private $xml;
	function __construct() {
		$this->sysinfo = new sysinfo();
		if (PSI_MBINFO) {
			$this->mbinfo = new mbinfo;
		}
		if (PSI_HDDTEMP) {
			$this->hddtemp = new hddtemp;
		}
		$this->xml = simplexml_load_string("<?xml version='1.0'?>\n<phpsysinfo></phpsysinfo>");
		$generation = $this->xml->addChild('Generation');
		$generation->addAttribute('version', PSI_VERSION);
		$generation->addAttribute('timestamp', time());
	}
	private function buildVitals() {
		$strLoadavg = '';
		$arrBuf = $this->sysinfo->loadavg(loadBar);
		foreach($arrBuf['avg'] as $strValue) {
			$strLoadavg.= $strValue . ' ';
		}
		$vitals = $this->xml->addChild('Vitals');
		if (useVhost === true) {
			$vitals->addChild('Hostname', $this->sysinfo->vhostname());
		} else {
			$vitals->addChild('Hostname', $this->sysinfo->chostname());
		}
		$vitals->addChild('IPAddr', $this->sysinfo->ip_addr());
		$vitals->addChild('Kernel', $this->sysinfo->kernel());
		$vitals->addChild('Distro', $this->sysinfo->distro());
		$vitals->addChild('Distroicon', $this->sysinfo->distroicon());
		$vitals->addChild('Uptime', $this->sysinfo->uptime());
		$vitals->addChild('Users', $this->sysinfo->users());
		$vitals->addChild('LoadAvg', $strLoadavg);
		if (isset($arrBuf['cpupercent'])) {
			$vitals->addChild('CPULoad', round($arrBuf['cpupercent'], 2));
		}
	}
	private function buildNetwork() {
		$arrNet = $this->sysinfo->network();
		$network = $this->xml->addChild('Network');
		foreach($arrNet as $strDev => $arrStats) {
			$device = $network->addChild('NetDevice');
			$device->addChild('Name', $strDev);
			$device->addChild('RxBytes', $arrStats['rx_bytes']);
			$device->addChild('TxBytes', $arrStats['tx_bytes']);
			$device->addChild('Errors', $arrStats['errs']);
			$device->addChild('Drops', $arrStats['drop']);
		}
	}
	private function buildHardware() {
		$hardware = $this->xml->addChild('Hardware');
		$cpu = $hardware->addChild('CPU');
		$pci = $hardware->addChild('PCI');
		$ide = $hardware->addChild('IDE');
		$usb = $hardware->addChild('USB');
		$scsi = $hardware->addChild('SCSI');
		$strPcidevices = "";
		$strIdedevices = "";
		$strUsbdevices = "";
		$strScsidevices = "";
		$arrSys = $this->sysinfo->cpu_info();
		$arrBuf = finddups($this->sysinfo->pci());
		if (count($arrBuf)) {
			for ($i = 0, $max = sizeof($arrBuf);$i < $max;$i++) {
				if ($arrBuf[$i]) {
					$tmp = $pci->addChild('Device');
					$tmp->addChild('Name', utf8_encode(trim(htmlspecialchars($arrBuf[$i]))));
				}
			}
		}
		$arrBuf = $this->sysinfo->ide();
		if (count($arrBuf)) {
			foreach($arrBuf as $strKey => $arrValue) {
				$tmp = $ide->addChild('Device');
				$tmp->addChild('Name', $strKey . ': ' . utf8_encode($arrValue['model']));
				if (isset($arrValue['capacity'])) {
					$tmp->addChild('Capacity', $arrValue['capacity']);
				}
			}
		}
		$arrBuf = $this->sysinfo->scsi();
		if (count($arrBuf)) {
			foreach($arrBuf as $strKey => $arrValue) {
				$tmp = $scsi->addChild('Device');
				if ($strKey >= '0' && $strKey <= '9') {
					$tmp->addChild('Name', utf8_encode($arrValue['model']));
				} else {
					$tmp->addChild('Name', $strKey . ': ' . utf8_encode($arrValue['model']));
				}
				if (isset($arrrValue['capacity'])) {
					$tmp->addChild('Capacity', $arrValue['capacity']);
				}
			}
		}
		$arrBuf = finddups($this->sysinfo->usb());
		if (count($arrBuf)) {
			for ($i = 0, $max = sizeof($arrBuf);$i < $max;$i++) {
				if (trim($arrBuf[$i]) != "") {
					$tmp = $usb->addChild('Device');
					$tmp->addChild('Name', utf8_encode(trim($arrBuf[$i])));
				}
			}
		}
		$_text = "  <Hardware>\n";
		$_text.= "    <CPU>\n";
		if (isset($arrSys['cpus'])) {
			$cpu->addChild('Number', $arrSys['cpus']);
		}
		if (isset($arrSys['model'])) {
			$cpu->addChild('Model', $arrSys['model']);
		}
		if (isset($arrSys['temp'])) {
			$cpu->addChild('Cputemp>', $arrSys['temp']);
		}
		if (isset($arrSys['cpuspeed'])) {
			$cpu->addChild('Cpuspeed', $arrSys['cpuspeed']);
		}
		if (isset($arrSys['busspeed'])) {
			$cpu->addChild('Busspeed', $arrSys['busspeed']);
		}
		if (isset($arrSys['cache'])) {
			$cpu->addChild('Cache', $arrSys['cache']);
		}
		if (isset($arrSys['bogomips'])) {
			$cpu->addChild('Bogomips', $arrSys['bogomips']);
		}
	}
	private function buildMemory() {
		$arrMem = $this->sysinfo->memory();
		$i = 0;
		$memory = $this->xml->addChild('Memory');
		$memory->addChild('Free', $arrMem['ram']['free']);
		$memory->addChild('Used', $arrMem['ram']['used']);
		$memory->addChild('Total', $arrMem['ram']['total']);
		$memory->addChild('Percent', $arrMem['ram']['percent']);
		if (isset($arrMem['ram']['app'])) {
			$memory->addChild('App', $arrMem['ram']['app']);
			$memory->addChild('AppPercent', $arrMem['ram']['app_percent']);
			$memory->addChild('Buffers', $arrMem['ram']['buffers']);
			$memory->addChild('BuffersPercent', $arrMem['ram']['buffers_percent']);
			$memory->addChild('Cached', $arrMem['ram']['cached']);
			$memory->addChild('CachedPercent', $arrMem['ram']['cached_percent']);
		}
		$swap = $this->xml->addChild('Swap');
		$swap->addChild('Free', $arrMem['swap']['free']);
		$swap->addChild('Used', $arrMem['swap']['used']);
		$swap->addChild('Total', $arrMem['swap']['total']);
		$swap->addChild('Percent', $arrMem['swap']['percent']);
		$swapDev = $this->xml->addChild('Swapdevices');
		foreach($arrMem['devswap'] as $arrDevice) {
			$swapMount = $swapDev->addChild('Mount');
			$swapMount->addChild('MountPointID', $i++);
			$swapMount->addChild('Type', 'Swap');
			$swapMount->addChild('Percent', $arrDevice['percent']);
			$swapMount->addChild('Free', $arrDevice['free']);
			$swapMount->addChild('Used', $arrDevice['used']);
			$swapMount->addChild('Size', $arrDevice['total']);
			$dev = $swapMount->addChild('Device');
			$dev->addChild('Name', $arrDevice['dev']);
		}
	}
	private function buildFilesystems() {
		$arrFs = $this->sysinfo->filesystems();
		$fs = $this->xml->addChild('FileSystem');
		for ($i = 0, $max = sizeof($arrFs);$i < $max;$i++) {
			$hideMounts = explode(',', hideMounts);
			$hideFstypes = explode(',', hideFstypes);
			if (!in_array($arrFs[$i]['mount'], $hideMounts) && !in_array($arrFs[$i]['fstype'], $hideFstypes)) {
				$mount = $fs->addChild('Mount');
				$mount->addchild('MountPointID', $i);
				if (showMountPoint === true) {
					$mount->addchild('MountPoint', $arrFs[$i]['mount']);
				}
				$mount->addchild('Type', $arrFs[$i]['fstype']);
				$mount->addchild('Percent', $arrFs[$i]['percent']);
				$mount->addchild('Free', $arrFs[$i]['free']);
				$mount->addchild('Used', $arrFs[$i]['used']);
				$mount->addchild('Size', $arrFs[$i]['size']);
				if (isset($arrFs[$i]['options'])) {
					$mount->addchild('Options', $arrFs[$i]['options']);
				}
				if (isset($arrFs[$i]['inodes'])) {
					$mount->addchild('Inodes', $arrFs[$i]['inodes']);
				}
				$dev = $mount->addchild('Device');
				$dev->addChild('Name', $arrFs[$i]['disk']);
			}
		}
	}
	private function buildMbinfo() {
		$mbinfo = $this->xml->addChild('MBinfo');
		$arrBuff = $this->mbinfo->temperature();
		if (sizeof($arrBuff) > 0) {
			$temp = $mbinfo->addChild('Temperature');
			foreach($arrBuff as $arrValue) {
				$item = $temp->addChild('Item');
				$item->addChild('Label', $arrValue['label']);
				$item->addChild('Value', $arrValue['value']);
				$item->addChild('Limit', $arrValue['limit']);
			}
		}
		$arrBuff = $this->mbinfo->fans();
		if (sizeof($arrBuff) > 0) {
			$fan = $mbinfo->addChild('Fan');
			foreach($arrBuff as $arrValue) {
				$item = $fan->addChild('Item');
				$item->addChild('Label', $arrValue['label']);
				$item->addChild('Value', $arrValue['value']);
				$item->addChild('Min', $arrValue['min']);
			}
		}
		$arrBuff = $this->mbinfo->voltage();
		if (sizeof($arrBuff) > 0) {
			$volt = $mbinfo->addChild('Voltage');
			foreach($arrBuff as $arrValue) {
				$item = $volt->addChild('Item');
				$item->addChild('Label', $arrValue['label']);
				$item->addChild('Value', $arrValue['value']);
				$item->addChild('Min', $arrValue['min']);
				$item->addChild('Max', $arrValue['max']);
			}
		}
	}
	private function buildHddtemp() {
		$arrBuf = $this->hddtemp->temperature(hddTemp);
		$hddtemp = $this->xml->addChild('HDDTemp');
		for ($i = 0, $max = sizeof($arrBuf);$i < $max;$i++) {
			$item = $hddtemp->addChild('Item');
			$item->addChild('Label', $arrBuf[$i]['label']);
			$item->addChild('Value', $arrBuf[$i]['value']);
			$item->addChild('Model', $arrBuf[$i]['model']);
		}
	}
	public function buildXml() {
		$this->buildVitals();
		$this->buildNetwork();
		$this->buildHardware();
		$this->buildMemory();
		$this->buildFilesystems();
		if (PSI_MBINFO) {
			$this->buildMbinfo();
		}
		if (PSI_HDDTEMP) {
			$this->buildHddtemp();
		}
	}
	public function printXml() {
		header("Content-Type: text/xml\n\n");
		echo $this->xml->asXML();
	}
	public function getXml() {
		return $this->xml->asXML();
	}
}
?>
