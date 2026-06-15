-- --------------------------------------------------------
-- Host:                         172.18.83.33
-- Server version:               10.4.21-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table oasys_dev.tbl_addressbook
CREATE TABLE IF NOT EXISTS `tbl_addressbook` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `company` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'0',
  `remarks` varchar(200) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `tbl_addressbook_username_IDX` (`username`) USING BTREE,
  KEY `tbl_addressbook_email_IDX` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4293 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advance
CREATE TABLE IF NOT EXISTS `tbl_advance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL,
  `AdvanceNo` varchar(100) DEFAULT NULL,
  `AdvanceForm` int(11) NOT NULL,
  `OpsCategory` int(11) NOT NULL,
  `createdby` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `RequestStatus` int(11) NOT NULL,
  `CompanyCode` varchar(100) DEFAULT NULL,
  `Beneficiary` varchar(100) DEFAULT NULL,
  `AccountName` varchar(100) DEFAULT NULL,
  `Bank` varchar(50) DEFAULT NULL,
  `AccountNumber` varchar(50) DEFAULT NULL,
  `DueDate` date DEFAULT NULL,
  `ExpectedDate` date DEFAULT NULL,
  `isBudgeted` int(1) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `isDeclaration` bit(1) DEFAULT NULL,
  `isUsed` int(2) NOT NULL DEFAULT 0,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advance_tbl_employee` (`employee_id`),
  KEY `createdby` (`createdby`)
) ENGINE=InnoDB AUTO_INCREMENT=5872 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advanceapproval
CREATE TABLE IF NOT EXISTS `tbl_advanceapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advanceapproval_tbl_advance` (`advance_id`),
  KEY `FK_tbl_advanceapproval_tbl_approver` (`approver_id`),
  CONSTRAINT `FK_tbl_advanceapproval_tbl_advance` FOREIGN KEY (`advance_id`) REFERENCES `tbl_advance` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tbl_advanceapproval_tbl_approver` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29412 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advanceattachment
CREATE TABLE IF NOT EXISTS `tbl_advanceattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL,
  `file_descr` varchar(255) NOT NULL,
  `file_loc` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `FK_tbl_advanceattachment_tbl_advance` (`advance_id`),
  CONSTRAINT `FK_tbl_advanceattachment_tbl_advance` FOREIGN KEY (`advance_id`) REFERENCES `tbl_advance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7982 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advancedetail
CREATE TABLE IF NOT EXISTS `tbl_advancedetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL,
  `Description` varchar(150) DEFAULT NULL,
  `AccountCode` varchar(100) DEFAULT NULL,
  `Amount` bigint(20) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancedetail_tbl_advance` (`advance_id`),
  CONSTRAINT `FK_tbl_advancedetail_tbl_advance` FOREIGN KEY (`advance_id`) REFERENCES `tbl_advance` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8823 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advancehistory
CREATE TABLE IF NOT EXISTS `tbl_advancehistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancehistory_tbl_advance` (`advance_id`),
  CONSTRAINT `FK_tbl_advancehistory_tbl_advance` FOREIGN KEY (`advance_id`) REFERENCES `tbl_advance` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36647 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpense
CREATE TABLE IF NOT EXISTS `tbl_advexpense` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL,
  `RequestStatus` int(11) NOT NULL,
  `ExpenseNo` varchar(100) DEFAULT NULL,
  `createdby` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `PaymentType` int(2) DEFAULT NULL,
  `AdvanceNo` varchar(100) DEFAULT NULL,
  `LessAdvance` int(11) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Bg` varchar(100) DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `Superior` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advance_tbl_employee` (`employee_id`),
  KEY `Superior` (`Superior`)
) ENGINE=InnoDB AUTO_INCREMENT=6447 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpenseapproval
CREATE TABLE IF NOT EXISTS `tbl_advexpenseapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advexpense_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advanceapproval_tbl_advance` (`advexpense_id`),
  KEY `FK_tbl_advanceapproval_tbl_approver` (`approver_id`),
  CONSTRAINT `FK_tbl_advexpenseapproval_tbl_advexpense` FOREIGN KEY (`advexpense_id`) REFERENCES `tbl_advexpense` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tbl_advexpenseapproval_tbl_approver` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17708 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpenseattachment
CREATE TABLE IF NOT EXISTS `tbl_advexpenseattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advexpense_id` int(11) NOT NULL,
  `file_descr` varchar(255) NOT NULL,
  `file_loc` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `FK_tbl_advanceattachment_tbl_advance` (`advexpense_id`),
  CONSTRAINT `FK_tbl_advpaymentattachment_copy_tbl_advexpense` FOREIGN KEY (`advexpense_id`) REFERENCES `tbl_advexpense` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11243 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpensedetail
CREATE TABLE IF NOT EXISTS `tbl_advexpensedetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advexpense_id` int(11) NOT NULL,
  `ExpenseType` varchar(150) DEFAULT NULL,
  `Purpose` varchar(255) DEFAULT NULL,
  `ReceiptDate` date DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `Currency` varchar(50) DEFAULT NULL,
  `ExchangeRate` int(11) DEFAULT NULL,
  `PaymentAmount` int(11) DEFAULT NULL,
  `CostCentre` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancedetail_tbl_advance` (`advexpense_id`),
  CONSTRAINT `FK_tbl_advexpensedetail_tbl_advexpense` FOREIGN KEY (`advexpense_id`) REFERENCES `tbl_advexpense` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19349 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpensedetail_bt
CREATE TABLE IF NOT EXISTS `tbl_advexpensedetail_bt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advexpense_id` int(11) NOT NULL,
  `DepartDate` date DEFAULT NULL,
  `DepartTime` time DEFAULT NULL,
  `ReturnDate` date DEFAULT NULL,
  `ReturnTime` time DEFAULT NULL,
  `Accom` int(5) DEFAULT 0,
  `Breakfast` int(5) DEFAULT 0,
  `Lunch` int(5) DEFAULT 0,
  `Dinner` int(5) DEFAULT 0,
  `Pocket` int(10) DEFAULT 0,
  `isPapua` int(5) DEFAULT 0,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancedetail_tbl_advance` (`advexpense_id`),
  CONSTRAINT `tbl_advexpensedetail_bt_ibfk_1` FOREIGN KEY (`advexpense_id`) REFERENCES `tbl_advexpense` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=303978 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpensehistory
CREATE TABLE IF NOT EXISTS `tbl_advexpensehistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advexpense_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancehistory_tbl_advance` (`advexpense_id`),
  CONSTRAINT `FK_tbl_advexpensehistory_tbl_advexpense` FOREIGN KEY (`advexpense_id`) REFERENCES `tbl_advexpense` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35390 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advexpsppd
CREATE TABLE IF NOT EXISTS `tbl_advexpsppd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) DEFAULT NULL,
  `breakfast` int(11) DEFAULT NULL,
  `lunch` int(11) DEFAULT NULL,
  `dinner` int(11) DEFAULT NULL,
  `pocket` int(11) DEFAULT NULL,
  `isPapua` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advpayment
CREATE TABLE IF NOT EXISTS `tbl_advpayment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL,
  `PaymentNo` varchar(150) DEFAULT NULL,
  `PaymentForm` int(11) NOT NULL,
  `OpsCategory` int(11) NOT NULL,
  `PaymentType` int(2) NOT NULL,
  `createdby` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `RequestStatus` int(11) NOT NULL,
  `AdvanceNo` varchar(100) DEFAULT NULL,
  `LessAdvance` int(11) DEFAULT NULL,
  `Payment` int(11) NOT NULL,
  `CompanyCode` varchar(100) DEFAULT NULL,
  `Beneficiary` varchar(100) DEFAULT NULL,
  `AccountName` varchar(100) DEFAULT NULL,
  `Bank` varchar(50) DEFAULT NULL,
  `AccountNumber` varchar(50) DEFAULT NULL,
  `NoVoucher` varchar(100) DEFAULT NULL,
  `DueDate` date DEFAULT NULL,
  `PaymentDate` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `isdeclaration` bit(1) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advance_tbl_employee` (`employee_id`),
  KEY `CreatedDate` (`CreatedDate`)
) ENGINE=InnoDB AUTO_INCREMENT=17637 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advpaymentapproval
CREATE TABLE IF NOT EXISTS `tbl_advpaymentapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advpayment_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advanceapproval_tbl_advance` (`advpayment_id`),
  KEY `FK_tbl_advanceapproval_tbl_approver` (`approver_id`),
  CONSTRAINT `FK_tbl_advpaymentapproval_tbl_advpayment` FOREIGN KEY (`advpayment_id`) REFERENCES `tbl_advpayment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tbl_advpaymentapproval_tbl_approver` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104872 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advpaymentattachment
CREATE TABLE IF NOT EXISTS `tbl_advpaymentattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advpayment_id` int(11) NOT NULL,
  `file_descr` varchar(255) NOT NULL,
  `file_loc` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `FK_tbl_advanceattachment_tbl_advance` (`advpayment_id`),
  CONSTRAINT `FK_tbl_advpaymentattachment_tbl_advpayment` FOREIGN KEY (`advpayment_id`) REFERENCES `tbl_advpayment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32452 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advpaymentdetail
CREATE TABLE IF NOT EXISTS `tbl_advpaymentdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advpayment_id` int(11) NOT NULL,
  `Description` varchar(150) DEFAULT NULL,
  `AccountCode` varchar(100) DEFAULT NULL,
  `Amount` bigint(20) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancedetail_tbl_advance` (`advpayment_id`),
  CONSTRAINT `FK_tbl_advpaymentdetail_tbl_advpayment` FOREIGN KEY (`advpayment_id`) REFERENCES `tbl_advpayment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31108 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_advpaymenthistory
CREATE TABLE IF NOT EXISTS `tbl_advpaymenthistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advpayment_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_advancehistory_tbl_advance` (`advpayment_id`),
  CONSTRAINT `FK_tbl_advpaymenthistory_tbl_advpayment` FOREIGN KEY (`advpayment_id`) REFERENCES `tbl_advpayment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106558 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_approvaltype
CREATE TABLE IF NOT EXISTS `tbl_approvaltype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Module` varchar(50) NOT NULL,
  `ApprovalType` varchar(50) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `isactive` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_approver
CREATE TABLE IF NOT EXISTS `tbl_approver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `approvaltype_id` int(11) NOT NULL,
  `isFinal` bit(1) NOT NULL DEFAULT b'0',
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `remarks` varchar(500) NOT NULL,
  `CompanyList` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_employee_id_approvaltype_id` (`module`,`employee_id`,`approvaltype_id`),
  KEY `employee_id` (`employee_id`),
  KEY `ApprovalType` (`approvaltype_id`),
  CONSTRAINT `FKApprovalType` FOREIGN KEY (`approvaltype_id`) REFERENCES `tbl_approvaltype` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10400 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_approver_copy1
CREATE TABLE IF NOT EXISTS `tbl_approver_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `approvaltype_id` int(11) NOT NULL,
  `isFinal` bit(1) NOT NULL DEFAULT b'0',
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `remarks` varchar(500) NOT NULL,
  `CompanyList` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_employee_id_approvaltype_id` (`module`,`employee_id`,`approvaltype_id`),
  KEY `employee_id` (`employee_id`),
  KEY `ApprovalType` (`approvaltype_id`),
  CONSTRAINT `tbl_approver_copy1_ibfk_1` FOREIGN KEY (`approvaltype_id`) REFERENCES `tbl_approvaltype` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_approver_copy2
CREATE TABLE IF NOT EXISTS `tbl_approver_copy2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `approvaltype_id` int(11) NOT NULL,
  `isFinal` bit(1) NOT NULL DEFAULT b'0',
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `remarks` varchar(500) NOT NULL,
  `CompanyList` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_employee_id_approvaltype_id` (`module`,`employee_id`,`approvaltype_id`),
  KEY `employee_id` (`employee_id`),
  KEY `ApprovalType` (`approvaltype_id`),
  CONSTRAINT `tbl_approver_copy2_ibfk_1` FOREIGN KEY (`approvaltype_id`) REFERENCES `tbl_approvaltype` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10062 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_approver_copy3
CREATE TABLE IF NOT EXISTS `tbl_approver_copy3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `approvaltype_id` int(11) NOT NULL,
  `isFinal` bit(1) NOT NULL DEFAULT b'0',
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `remarks` varchar(500) NOT NULL,
  `CompanyList` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_employee_id_approvaltype_id` (`module`,`employee_id`,`approvaltype_id`),
  KEY `employee_id` (`employee_id`),
  KEY `ApprovalType` (`approvaltype_id`),
  CONSTRAINT `tbl_approver_copy3_ibfk_1` FOREIGN KEY (`approvaltype_id`) REFERENCES `tbl_approvaltype` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10064 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail
CREATE TABLE IF NOT EXISTS `tbl_audittrail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `olddata` varchar(21844) DEFAULT NULL,
  `newdata` varchar(21844) DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1754754 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail2020
CREATE TABLE IF NOT EXISTS `tbl_audittrail2020` (
  `id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `action` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `olddata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `newdata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail2021
CREATE TABLE IF NOT EXISTS `tbl_audittrail2021` (
  `id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `action` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `olddata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `newdata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail2022
CREATE TABLE IF NOT EXISTS `tbl_audittrail2022` (
  `id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `action` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `olddata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `newdata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail2023
CREATE TABLE IF NOT EXISTS `tbl_audittrail2023` (
  `id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `action` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `olddata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `newdata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_audittrail2024
CREATE TABLE IF NOT EXISTS `tbl_audittrail2024` (
  `id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `action` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `olddata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `newdata` varchar(21844) CHARACTER SET latin1 DEFAULT NULL,
  `action_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_company
CREATE TABLE IF NOT EXISTS `tbl_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `CompanyCode` varchar(5) DEFAULT NULL,
  `CompanyName` varchar(100) DEFAULT NULL,
  `CompanyAddress` varchar(100) DEFAULT NULL,
  `CompanyLocation` varchar(100) DEFAULT NULL,
  `Logo` varchar(100) DEFAULT NULL,
  `Kop` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isUsed` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_company_copy1
CREATE TABLE IF NOT EXISTS `tbl_company_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `CompanyCode` varchar(5) DEFAULT NULL,
  `CompanyName` varchar(100) DEFAULT NULL,
  `CompanyAddress` varchar(100) DEFAULT NULL,
  `CompanyLocation` varchar(100) DEFAULT NULL,
  `Logo` varchar(100) DEFAULT NULL,
  `Kop` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isUsed` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_contract
CREATE TABLE IF NOT EXISTS `tbl_contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `ContractNo` varchar(200) NOT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `PeriodStart` date NOT NULL,
  `PeriodEnd` date NOT NULL,
  `Description` text DEFAULT NULL,
  `ContractStatus` int(11) NOT NULL DEFAULT 0,
  `OldContractNo` int(11) DEFAULT NULL,
  `NewContractNo` int(11) DEFAULT NULL,
  `ContractDocument` varchar(400) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT NULL,
  `ModifiedBy` int(11) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `tbl_contract_contractor_id_IDX` (`contractor_id`) USING BTREE,
  KEY `tbl_contract_CreatedBy_IDX` (`CreatedBy`) USING BTREE,
  KEY `tbl_contract_rfc_id_IDX` (`rfc_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2198 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_contractdoc
CREATE TABLE IF NOT EXISTS `tbl_contractdoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(500) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  `is_active` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `FKContractDoc` FOREIGN KEY (`contract_id`) REFERENCES `tbl_contract` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2010 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_currency
CREATE TABLE IF NOT EXISTS `tbl_currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `detail` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_dayoffapproval
CREATE TABLE IF NOT EXISTS `tbl_dayoffapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dayoff_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` date DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dayoff_id_approver_id` (`dayoff_id`,`approver_id`),
  KEY `request_id` (`dayoff_id`),
  KEY `employee_id` (`approver_id`),
  CONSTRAINT `FKDayofReqApp` FOREIGN KEY (`dayoff_id`) REFERENCES `tbl_dayoffreq` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKDayoffEmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109191 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_dayoffdetail
CREATE TABLE IF NOT EXISTS `tbl_dayoffdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dayoff_id` int(11) NOT NULL,
  `DateWorked` date DEFAULT NULL,
  `startDate` datetime DEFAULT NULL,
  `Reason` varchar(100) DEFAULT NULL,
  `Achievement` varchar(100) DEFAULT NULL,
  `isApproved` bit(1) DEFAULT NULL,
  `isUsed` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dayoff_id` (`dayoff_id`,`DateWorked`),
  KEY `request_id` (`dayoff_id`),
  CONSTRAINT `FKReqDayoff` FOREIGN KEY (`dayoff_id`) REFERENCES `tbl_dayoffreq` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29545 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_dayoffhistory
CREATE TABLE IF NOT EXISTS `tbl_dayoffhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dayoff_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dayoffid` (`dayoff_id`),
  CONSTRAINT `FKdayoff` FOREIGN KEY (`dayoff_id`) REFERENCES `tbl_dayoffreq` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=152939 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_dayoffreq
CREATE TABLE IF NOT EXISTS `tbl_dayoffreq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedBy` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `RequestDate` date DEFAULT NULL,
  `RequestStatus` int(11) DEFAULT NULL,
  `Superior` int(11) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `Superior` (`Superior`),
  KEY `DeptHead` (`DeptHead`),
  KEY `createdby` (`CreatedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=33214 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_dayoffsub
CREATE TABLE IF NOT EXISTS `tbl_dayoffsub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_id` int(11) DEFAULT NULL,
  `dayoffdetail_id` int(11) DEFAULT NULL,
  `isApproved` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `request_id` (`leave_id`),
  KEY `dayoff_id` (`dayoffdetail_id`),
  CONSTRAINT `FKDayofSub` FOREIGN KEY (`dayoffdetail_id`) REFERENCES `tbl_dayoffdetail` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKLeaveReqSub` FOREIGN KEY (`leave_id`) REFERENCES `tbl_leavereq` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_department
CREATE TABLE IF NOT EXISTS `tbl_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `DepartmentName` varchar(250) DEFAULT NULL,
  `DepartmentGroup` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isUsed` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_department_copy1
CREATE TABLE IF NOT EXISTS `tbl_department_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `DepartmentName` varchar(250) DEFAULT NULL,
  `DepartmentGroup` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isUsed` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_designation
CREATE TABLE IF NOT EXISTS `tbl_designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `division_id` int(11) DEFAULT NULL,
  `SAPCode` varchar(50) DEFAULT NULL,
  `DesignationName` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`),
  CONSTRAINT `FKDivision` FOREIGN KEY (`division_id`) REFERENCES `tbl_division` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2464 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_designation_copy1
CREATE TABLE IF NOT EXISTS `tbl_designation_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `division_id` int(11) DEFAULT NULL,
  `SAPCode` varchar(50) DEFAULT NULL,
  `DesignationName` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`),
  CONSTRAINT `tbl_designation_copy1_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `tbl_division` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2130 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_detailleave
CREATE TABLE IF NOT EXISTS `tbl_detailleave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_id` int(11) NOT NULL,
  `leavetype_id` int(11) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `NumDays` int(11) NOT NULL,
  `isApproved` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `request_id` (`leave_id`),
  KEY `leavetype_id` (`leavetype_id`),
  CONSTRAINT `FKLEaveType` FOREIGN KEY (`leavetype_id`) REFERENCES `tbl_leavetype` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKReqId` FOREIGN KEY (`leave_id`) REFERENCES `tbl_leavereq` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_division
CREATE TABLE IF NOT EXISTS `tbl_division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `DivisionName` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `FKDepartment` FOREIGN KEY (`department_id`) REFERENCES `tbl_department` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_empjoindate
CREATE TABLE IF NOT EXISTS `tbl_empjoindate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPID` varchar(50) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `joindate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1146 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee
CREATE TABLE IF NOT EXISTS `tbl_employee` (
  `id` int(11) NOT NULL,
  `sys_id` varchar(50) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `BirthOfDate` date DEFAULT NULL,
  `Gender` varchar(20) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `companycode` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `isInternationalStaff` varchar(1) NOT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isActive` varchar(1) NOT NULL,
  `isTerminate` varchar(1) NOT NULL,
  `isNotHC` varchar(1) NOT NULL,
  `sys_id_depthead` varchar(50) DEFAULT NULL,
  `deptheadName` varchar(100) DEFAULT NULL,
  `sys_id_superior` varchar(50) DEFAULT NULL,
  `superiorName` varchar(100) DEFAULT NULL,
  `isPIC` varchar(1) DEFAULT NULL,
  `contract_status` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id_copy1` (`department_id`),
  KEY `division_id_copy1` (`division_id`),
  KEY `designation_id_copy1` (`designation_id`),
  KEY `grade_id_copy1` (`grade_id`),
  KEY `company_id_copy1` (`company_id`),
  KEY `religion_id_copy1` (`religion_id`),
  KEY `location_id_copy1` (`location_id`),
  KEY `level_id_copy1` (`level_id`),
  KEY `tbl_employee_SAPID_IDX_copy1` (`SAPID`),
  KEY `tbl_employee_companycode_IDX_copy1` (`companycode`),
  KEY `tbl_employee_LoginName_IDX` (`LoginName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employeetrial
CREATE TABLE IF NOT EXISTS `tbl_employeetrial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `BirthOfDate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1354 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_00
CREATE TABLE IF NOT EXISTS `tbl_employee_00` (
  `id` int(11) NOT NULL,
  `sys_id` varchar(50) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `BirthOfDate` date DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `companycode` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `isInternationalStaff` varchar(1) NOT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isActive` varchar(1) NOT NULL,
  `isTerminate` varchar(1) NOT NULL,
  `isNotHC` varchar(1) NOT NULL,
  `sys_id_superior` varchar(50) DEFAULT NULL,
  `superiorName` varchar(100) DEFAULT NULL,
  `sys_id_depthead` varchar(50) DEFAULT NULL,
  `deptheadName` varchar(100) DEFAULT NULL,
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`),
  KEY `tbl_employee_companycode_IDX` (`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_01
CREATE TABLE IF NOT EXISTS `tbl_employee_01` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3613 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_02
CREATE TABLE IF NOT EXISTS `tbl_employee_02` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3533 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_03
CREATE TABLE IF NOT EXISTS `tbl_employee_03` (
  `id` int(11) NOT NULL,
  `sys_id` varchar(50) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `BirthOfDate` date DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `companycode` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `isInternationalStaff` varchar(1) NOT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isActive` varchar(1) NOT NULL,
  `isTerminate` varchar(1) NOT NULL,
  `isNotHC` varchar(1) NOT NULL,
  `sys_id_depthead` varchar(50) DEFAULT NULL,
  `deptheadName` varchar(100) DEFAULT NULL,
  `sys_id_superior` varchar(50) DEFAULT NULL,
  `superiorName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id_copy1` (`department_id`),
  KEY `division_id_copy1` (`division_id`),
  KEY `designation_id_copy1` (`designation_id`),
  KEY `grade_id_copy1` (`grade_id`),
  KEY `company_id_copy1` (`company_id`),
  KEY `religion_id_copy1` (`religion_id`),
  KEY `location_id_copy1` (`location_id`),
  KEY `level_id_copy1` (`level_id`),
  KEY `tbl_employee_SAPID_IDX_copy1` (`SAPID`),
  KEY `tbl_employee_companycode_IDX_copy1` (`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_0522
CREATE TABLE IF NOT EXISTS `tbl_employee_0522` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `division_id` (`division_id`) USING BTREE,
  KEY `designation_id` (`designation_id`) USING BTREE,
  KEY `grade_id` (`grade_id`) USING BTREE,
  KEY `company_id` (`company_id`) USING BTREE,
  KEY `religion_id` (`religion_id`) USING BTREE,
  KEY `location_id` (`location_id`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE,
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1702 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_0722
CREATE TABLE IF NOT EXISTS `tbl_employee_0722` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `division_id` (`division_id`) USING BTREE,
  KEY `designation_id` (`designation_id`) USING BTREE,
  KEY `grade_id` (`grade_id`) USING BTREE,
  KEY `company_id` (`company_id`) USING BTREE,
  KEY `religion_id` (`religion_id`) USING BTREE,
  KEY `location_id` (`location_id`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE,
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1712 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_0923
CREATE TABLE IF NOT EXISTS `tbl_employee_0923` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `division_id` (`division_id`) USING BTREE,
  KEY `designation_id` (`designation_id`) USING BTREE,
  KEY `grade_id` (`grade_id`) USING BTREE,
  KEY `company_id` (`company_id`) USING BTREE,
  KEY `religion_id` (`religion_id`) USING BTREE,
  KEY `location_id` (`location_id`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE,
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2022 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_2311
CREATE TABLE IF NOT EXISTS `tbl_employee_2311` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `division_id` (`division_id`) USING BTREE,
  KEY `designation_id` (`designation_id`) USING BTREE,
  KEY `grade_id` (`grade_id`) USING BTREE,
  KEY `company_id` (`company_id`) USING BTREE,
  KEY `religion_id` (`religion_id`) USING BTREE,
  KEY `location_id` (`location_id`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE,
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3461 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_copy1
CREATE TABLE IF NOT EXISTS `tbl_employee_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3519 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_copy2
CREATE TABLE IF NOT EXISTS `tbl_employee_copy2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(10) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `division_id` (`division_id`),
  KEY `designation_id` (`designation_id`),
  KEY `grade_id` (`grade_id`),
  KEY `company_id` (`company_id`),
  KEY `religion_id` (`religion_id`),
  KEY `location_id` (`location_id`),
  KEY `level_id` (`level_id`),
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3533 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_employee_master
CREATE TABLE IF NOT EXISTS `tbl_employee_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `SAPID` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `Gender` varchar(2) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `MaritalStatus` varchar(10) DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  `isInternationalStaff` bit(1) NOT NULL DEFAULT b'0',
  `companycode` varchar(5) DEFAULT NULL,
  `CostCenter` varchar(100) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `division_id` (`division_id`) USING BTREE,
  KEY `designation_id` (`designation_id`) USING BTREE,
  KEY `grade_id` (`grade_id`) USING BTREE,
  KEY `company_id` (`company_id`) USING BTREE,
  KEY `religion_id` (`religion_id`) USING BTREE,
  KEY `location_id` (`location_id`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE,
  KEY `tbl_employee_SAPID_IDX` (`SAPID`) USING BTREE,
  KEY `tbl_employee_companycode_IDX` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1608 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_entitledleave
CREATE TABLE IF NOT EXISTS `tbl_entitledleave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `leavetype_id` int(11) DEFAULT NULL,
  `MaxDay` int(11) DEFAULT NULL,
  `Taken` int(11) DEFAULT NULL,
  `Balance` int(11) DEFAULT NULL,
  `ValidStart` date DEFAULT NULL,
  `ValidEnd` date DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `leavetype_id` (`leavetype_id`),
  CONSTRAINT `FKEmpEntitledLeave` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee_02` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKLeaveTypeEnt` FOREIGN KEY (`leavetype_id`) REFERENCES `tbl_leavetype` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_errorlog
CREATE TABLE IF NOT EXISTS `tbl_errorlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ErrorType` varchar(50) NOT NULL,
  `ErrorDate` datetime NOT NULL,
  `ErrorMessage` text NOT NULL,
  `User` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99861 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_expensetype
CREATE TABLE IF NOT EXISTS `tbl_expensetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_grade
CREATE TABLE IF NOT EXISTS `tbl_grade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Grade` varchar(50) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_holiday
CREATE TABLE IF NOT EXISTS `tbl_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `HolidayDate` date NOT NULL,
  `Description` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `HolidayDate` (`HolidayDate`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_internalhiring
CREATE TABLE IF NOT EXISTS `tbl_internalhiring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postno` varchar(50) DEFAULT NULL,
  `bu` varchar(50) DEFAULT NULL,
  `department` varchar(200) DEFAULT NULL,
  `WorkLocation` varchar(100) DEFAULT NULL,
  `position` varchar(200) DEFAULT NULL,
  `positioncode` varchar(200) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `ExpiredDate` date DEFAULT NULL,
  `batch` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_internalhiringdetail
CREATE TABLE IF NOT EXISTS `tbl_internalhiringdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(8) DEFAULT NULL,
  `ih_id` int(11) DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `SAPID` varchar(50) DEFAULT NULL,
  `passcode` varchar(100) DEFAULT NULL,
  `postno` varchar(50) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `nohp` varchar(100) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `age` int(8) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `LOS` int(8) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `educationothers` varchar(255) DEFAULT NULL,
  `reasonmove` varchar(255) DEFAULT NULL,
  `reasondeserve` varchar(255) DEFAULT NULL,
  `reasoncontribution` varchar(255) DEFAULT NULL,
  `score1` varchar(50) DEFAULT NULL,
  `score2` varchar(50) DEFAULT NULL,
  `score3` varchar(50) DEFAULT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `isDeclaration` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `SAPID` (`SAPID`),
  KEY `company_id` (`company_id`),
  KEY `department_id` (`department_id`),
  KEY `location_id` (`location_id`),
  KEY `designation_id` (`designation_id`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_iteie
CREATE TABLE IF NOT EXISTS `tbl_iteie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `EmployeeID` varchar(50) DEFAULT NULL,
  `DepartmentUser` varchar(50) DEFAULT NULL,
  `Designation` varchar(50) DEFAULT NULL,
  `BgBu` varchar(50) DEFAULT NULL,
  `OfficeLocation` varchar(50) DEFAULT NULL,
  `Floor` int(11) DEFAULT NULL,
  `PhoneExt` varchar(50) DEFAULT NULL,
  `isVip` bit(1) DEFAULT NULL,
  `RequestType` int(11) DEFAULT NULL,
  `AccessType` int(11) DEFAULT NULL,
  `AccountType` int(11) DEFAULT NULL,
  `ValidFrom` date DEFAULT NULL,
  `ValidTo` date DEFAULT NULL,
  `ListGroup` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1298 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_iteieapproval
CREATE TABLE IF NOT EXISTS `tbl_iteieapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iteie_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iteie_id_approver_id` (`iteie_id`,`approver_id`),
  KEY `iteie_id` (`iteie_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `FKITEIEmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKITEIReqApp` FOREIGN KEY (`iteie_id`) REFERENCES `tbl_iteie` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4460 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_iteiehistory
CREATE TABLE IF NOT EXISTS `tbl_iteiehistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iteie_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iteie_id` (`iteie_id`),
  CONSTRAINT `FKitei` FOREIGN KEY (`iteie_id`) REFERENCES `tbl_iteie` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4925 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itimail
CREATE TABLE IF NOT EXISTS `tbl_itimail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `FormType` int(11) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL,
  `Designation` varchar(50) DEFAULT NULL,
  `BgBu` varchar(50) DEFAULT NULL,
  `OfficeLocation` varchar(50) DEFAULT NULL,
  `Floor` int(11) DEFAULT NULL,
  `PhoneExt` varchar(50) DEFAULT NULL,
  `AccessRequested` int(11) DEFAULT NULL,
  `AccessType` int(11) DEFAULT NULL,
  `AccountType` int(11) DEFAULT NULL,
  `EmailQuota` int(11) DEFAULT NULL,
  `ValidFrom` date DEFAULT NULL,
  `ValidTo` date DEFAULT NULL,
  `EmailDomain` int(11) DEFAULT NULL,
  `ListGroup` varchar(50) DEFAULT NULL,
  `ListGroupModeration` int(11) DEFAULT NULL,
  `web1` varchar(100) DEFAULT NULL,
  `web2` varchar(100) DEFAULT NULL,
  `NewMailBoxSize` int(11) DEFAULT NULL,
  `IncomingSize` int(11) DEFAULT NULL,
  `OutgoingSize` int(11) DEFAULT NULL,
  `TypeOfAccess` varchar(200) DEFAULT NULL,
  `EmailGroupName` varchar(100) DEFAULT NULL,
  `MemberName` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `isDeclaration` bit(1) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3376 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itimailapproval
CREATE TABLE IF NOT EXISTS `tbl_itimailapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itimail_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `itimail_id_approver_id` (`itimail_id`,`approver_id`),
  KEY `itimail_id` (`itimail_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `FKITIMAILReqApp` FOREIGN KEY (`itimail_id`) REFERENCES `tbl_itimail` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKITIMAILmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10470 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itimailhistory
CREATE TABLE IF NOT EXISTS `tbl_itimailhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itimail_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itimail_id` (`itimail_id`),
  CONSTRAINT `FKitimail` FOREIGN KEY (`itimail_id`) REFERENCES `tbl_itimail` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11513 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itsharef
CREATE TABLE IF NOT EXISTS `tbl_itsharef` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  `Designation` varchar(50) DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL,
  `Grade` varchar(50) DEFAULT NULL,
  `BgBu` varchar(50) DEFAULT NULL,
  `OfficeLocation` varchar(100) DEFAULT NULL,
  `Floor` int(11) DEFAULT 0,
  `PhoneExt` varchar(50) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `FolderName` varchar(100) DEFAULT NULL,
  `RequestType` int(11) NOT NULL,
  `AccountType` int(11) DEFAULT NULL,
  `ValidFrom` datetime DEFAULT NULL,
  `ValidTo` datetime DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `isDeclaration` bit(1) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_itsharef_tbl_employee` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itsharefapproval
CREATE TABLE IF NOT EXISTS `tbl_itsharefapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itsharef_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itsharef_id` (`itsharef_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `FK_tbl_itsharefapproval_tbl_approver` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tbl_itsharefapproval_tbl_itsharef` FOREIGN KEY (`itsharef_id`) REFERENCES `tbl_itsharef` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5912 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itsharefattachment
CREATE TABLE IF NOT EXISTS `tbl_itsharefattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itsharef_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(100) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itsharef_id` (`itsharef_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FK_tbl_itsharefattachment_tbl_itsharef` FOREIGN KEY (`itsharef_id`) REFERENCES `tbl_itsharef` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itsharefdetail
CREATE TABLE IF NOT EXISTS `tbl_itsharefdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itsharef_id` int(11) NOT NULL,
  `FolderName` varchar(150) DEFAULT NULL,
  `RequestType` int(11) DEFAULT NULL,
  `GrantAccessTo` varchar(100) DEFAULT NULL,
  `Change` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_itsharefdetail_tbl_itsharef` (`itsharef_id`),
  CONSTRAINT `FK_tbl_itsharefdetail_tbl_itsharef` FOREIGN KEY (`itsharef_id`) REFERENCES `tbl_itsharef` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3766 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_itsharefhistory
CREATE TABLE IF NOT EXISTS `tbl_itsharefhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itsharef_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tbl_itsharefhistory_tbl_itsharef` (`itsharef_id`),
  CONSTRAINT `FK_tbl_itsharefhistory_tbl_itsharef` FOREIGN KEY (`itsharef_id`) REFERENCES `tbl_itsharef` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9186 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_leavereq
CREATE TABLE IF NOT EXISTS `tbl_leavereq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `RequestDate` date DEFAULT NULL,
  `RequestStatus` int(11) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FKEmpRequest` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee_02` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_leavetype
CREATE TABLE IF NOT EXISTS `tbl_leavetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `LeaveType` varchar(100) DEFAULT NULL,
  `MaxDay` int(11) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_level
CREATE TABLE IF NOT EXISTS `tbl_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Level` varchar(50) DEFAULT NULL,
  `Remarks` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_location
CREATE TABLE IF NOT EXISTS `tbl_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `isUsed` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_location_copy
CREATE TABLE IF NOT EXISTS `tbl_location_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_location_copy1
CREATE TABLE IF NOT EXISTS `tbl_location_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SAPCode` varchar(50) DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mailrecipient
CREATE TABLE IF NOT EXISTS `tbl_mailrecipient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) DEFAULT NULL,
  `company_list` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `isActive` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf28
CREATE TABLE IF NOT EXISTS `tbl_mmf28` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `WONumber` varchar(50) DEFAULT NULL,
  `MMFNumber` varchar(50) DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL,
  `TelpNo` varchar(50) DEFAULT NULL,
  `ChargeCode` varchar(50) DEFAULT NULL,
  `MaterialDispatch` varchar(100) DEFAULT NULL,
  `RequiredDate` datetime DEFAULT NULL,
  `MaterialCode` varchar(50) DEFAULT NULL,
  `MaterialDescr` varchar(100) DEFAULT NULL,
  `Symptomps` varchar(300) DEFAULT NULL,
  `RequiredType` int(11) NOT NULL,
  `RequiredOther` varchar(100) DEFAULT NULL,
  `Instruction` varchar(300) DEFAULT NULL,
  `isHazardousChemical` bit(1) NOT NULL DEFAULT b'0',
  `HazChemicalName` varchar(50) DEFAULT NULL,
  `isDecontaminated` bit(1) NOT NULL DEFAULT b'0',
  `isNotContaminated` bit(1) NOT NULL DEFAULT b'0',
  `NotContaminatedReason` varchar(50) DEFAULT NULL,
  `isNonHazardous` bit(1) NOT NULL DEFAULT b'0',
  `NonHazChemicalName` varchar(50) DEFAULT NULL,
  `isNonChemical` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` text DEFAULT NULL,
  `MaterialDispatchNo` varchar(100) DEFAULT NULL,
  `isRepair` bit(1) NOT NULL DEFAULT b'0',
  `isScrap` bit(1) NOT NULL DEFAULT b'0',
  `EstimateCost` double DEFAULT NULL,
  `PONo` varchar(50) DEFAULT NULL,
  `MaterialReturnedDate` datetime DEFAULT NULL,
  `SupplierDODNNo` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `Buyer` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `Buyer` (`Buyer`)
) ENGINE=InnoDB AUTO_INCREMENT=1346 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf28approval
CREATE TABLE IF NOT EXISTS `tbl_mmf28approval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf28_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mmf28_id_approver_id` (`mmf28_id`,`approver_id`),
  KEY `request_id` (`mmf28_id`),
  KEY `employee_id` (`approver_id`),
  CONSTRAINT `FKMMF28EmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKMMF28ReqApp` FOREIGN KEY (`mmf28_id`) REFERENCES `tbl_mmf28` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3168 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf28attachment
CREATE TABLE IF NOT EXISTS `tbl_mmf28attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf28_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(100) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mmf28_id` (`mmf28_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FKMMF28Attachment` FOREIGN KEY (`mmf28_id`) REFERENCES `tbl_mmf28` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1026 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf28history
CREATE TABLE IF NOT EXISTS `tbl_mmf28history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf28_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mmf28id` (`mmf28_id`),
  CONSTRAINT `FKmmf28` FOREIGN KEY (`mmf28_id`) REFERENCES `tbl_mmf28` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4493 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf28_copy1
CREATE TABLE IF NOT EXISTS `tbl_mmf28_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `WONumber` varchar(50) DEFAULT NULL,
  `MMFNumber` varchar(50) DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL,
  `TelpNo` varchar(50) DEFAULT NULL,
  `ChargeCode` varchar(50) DEFAULT NULL,
  `MaterialDispatch` varchar(100) DEFAULT NULL,
  `RequiredDate` datetime DEFAULT NULL,
  `MaterialCode` varchar(50) DEFAULT NULL,
  `MaterialDescr` varchar(100) DEFAULT NULL,
  `Symptomps` varchar(300) DEFAULT NULL,
  `RequiredType` int(11) NOT NULL,
  `RequiredOther` varchar(100) DEFAULT NULL,
  `Instruction` varchar(300) DEFAULT NULL,
  `isHazardousChemical` bit(1) NOT NULL DEFAULT b'0',
  `HazChemicalName` varchar(50) DEFAULT NULL,
  `isDecontaminated` bit(1) NOT NULL DEFAULT b'0',
  `isNotContaminated` bit(1) NOT NULL DEFAULT b'0',
  `NotContaminatedReason` varchar(50) DEFAULT NULL,
  `isNonHazardous` bit(1) NOT NULL DEFAULT b'0',
  `NonHazChemicalName` varchar(50) DEFAULT NULL,
  `isNonChemical` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` text DEFAULT NULL,
  `MaterialDispatchNo` varchar(100) DEFAULT NULL,
  `isRepair` bit(1) NOT NULL DEFAULT b'0',
  `isScrap` bit(1) NOT NULL DEFAULT b'0',
  `EstimateCost` double DEFAULT NULL,
  `PONo` varchar(50) DEFAULT NULL,
  `MaterialReturnedDate` datetime DEFAULT NULL,
  `SupplierDODNNo` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `Buyer` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `Buyer` (`Buyer`)
) ENGINE=InnoDB AUTO_INCREMENT=1310 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30
CREATE TABLE IF NOT EXISTS `tbl_mmf30` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `RequestStatus` int(11) NOT NULL,
  `PRType` int(11) NOT NULL,
  `RequisitionType` int(11) NOT NULL,
  `RequisitionOther` varchar(50) DEFAULT NULL,
  `PRNo` varchar(50) DEFAULT NULL,
  `DeliverTo` varchar(50) DEFAULT NULL,
  `CostCode` varchar(50) DEFAULT NULL,
  `CostElement` varchar(50) DEFAULT NULL,
  `SupplierName` varchar(50) DEFAULT NULL,
  `SupplierAddress` varchar(50) DEFAULT NULL,
  `SupplierEmailFax` varchar(50) DEFAULT NULL,
  `ContractNo` varchar(50) DEFAULT NULL,
  `ReceivedOn` date DEFAULT NULL,
  `ReceivedByName` varchar(100) DEFAULT NULL,
  `SupplierContractNo` varchar(100) DEFAULT NULL,
  `Reason` varchar(300) DEFAULT NULL,
  `RemarksU` text NOT NULL,
  `ProcComments` text NOT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `Buyer` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `Buyer` (`Buyer`)
) ENGINE=InnoDB AUTO_INCREMENT=9222 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30approval
CREATE TABLE IF NOT EXISTS `tbl_mmf30approval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf30_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  `ProcComments` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mmf30_id_approver_id` (`mmf30_id`,`approver_id`),
  KEY `request_id` (`mmf30_id`),
  KEY `employee_id` (`approver_id`),
  CONSTRAINT `FKMMF30EmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKMMF30ReqApp` FOREIGN KEY (`mmf30_id`) REFERENCES `tbl_mmf30` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25518 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30attachment
CREATE TABLE IF NOT EXISTS `tbl_mmf30attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf30_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(100) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mmf30_id` (`mmf30_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FKMMF30Attachment` FOREIGN KEY (`mmf30_id`) REFERENCES `tbl_mmf30` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7772 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30detail
CREATE TABLE IF NOT EXISTS `tbl_mmf30detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf30_id` int(11) NOT NULL,
  `MaterialCode` varchar(50) DEFAULT NULL,
  `MaterialDescr` varchar(400) DEFAULT NULL,
  `PartNumber` varchar(50) DEFAULT NULL,
  `BrandManufacturer` varchar(100) DEFAULT NULL,
  `Qty` double NOT NULL DEFAULT 0,
  `Unit` varchar(50) DEFAULT NULL,
  `Currency` varchar(50) DEFAULT NULL,
  `UnitPrice` double NOT NULL DEFAULT 0,
  `ExtendedPrice` double NOT NULL DEFAULT 0,
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mmf30_id` (`mmf30_id`),
  CONSTRAINT `FKMMF30Detail` FOREIGN KEY (`mmf30_id`) REFERENCES `tbl_mmf30` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35613 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30history
CREATE TABLE IF NOT EXISTS `tbl_mmf30history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mmf30_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mmf30id` (`mmf30_id`),
  CONSTRAINT `FKmmf30` FOREIGN KEY (`mmf30_id`) REFERENCES `tbl_mmf30` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38881 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mmf30_copy1
CREATE TABLE IF NOT EXISTS `tbl_mmf30_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `RequestStatus` int(11) NOT NULL,
  `PRType` int(11) NOT NULL,
  `RequisitionType` int(11) NOT NULL,
  `RequisitionOther` varchar(50) DEFAULT NULL,
  `PRNo` varchar(50) DEFAULT NULL,
  `DeliverTo` varchar(50) DEFAULT NULL,
  `CostCode` varchar(50) DEFAULT NULL,
  `CostElement` varchar(50) DEFAULT NULL,
  `SupplierName` varchar(50) DEFAULT NULL,
  `SupplierAddress` varchar(50) DEFAULT NULL,
  `SupplierEmailFax` varchar(50) DEFAULT NULL,
  `ContractNo` varchar(50) DEFAULT NULL,
  `ReceivedOn` date DEFAULT NULL,
  `ReceivedByName` varchar(100) DEFAULT NULL,
  `SupplierContractNo` varchar(100) DEFAULT NULL,
  `Reason` varchar(300) DEFAULT NULL,
  `RemarksU` text NOT NULL,
  `ProcComments` text NOT NULL,
  `DeptHead` int(11) DEFAULT NULL,
  `Buyer` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `Buyer` (`Buyer`)
) ENGINE=InnoDB AUTO_INCREMENT=8918 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_mod
CREATE TABLE IF NOT EXISTS `tbl_mod` (
  `id` int(10) unsigned NOT NULL,
  `mod` varchar(50) NOT NULL,
  `isActive` int(3) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_module
CREATE TABLE IF NOT EXISTS `tbl_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_religion
CREATE TABLE IF NOT EXISTS `tbl_religion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Religion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfc
CREATE TABLE IF NOT EXISTS `tbl_rfc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `RequestStatus` int(11) DEFAULT NULL,
  `CompanyCode` varchar(50) NOT NULL,
  `depthead` int(11) DEFAULT NULL,
  `RFCNo` varchar(50) DEFAULT NULL,
  `RFQNo` varchar(50) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `RFCType` varchar(50) DEFAULT NULL,
  `isProjectCapex` bit(1) DEFAULT b'0',
  `OldContractNo` varchar(50) DEFAULT NULL,
  `PeriodStart` date DEFAULT NULL,
  `PeriodEnd` date DEFAULT NULL,
  `RateType` varchar(50) DEFAULT NULL,
  `Rate` varchar(100) DEFAULT NULL,
  `SKNo` varchar(100) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `contractor_id2` int(11) DEFAULT NULL,
  `procurement_rate` varchar(50) DEFAULT NULL,
  `procurement_contractor_id` int(11) DEFAULT NULL,
  `PaymentTerm` varchar(50) DEFAULT NULL,
  `ContractNo` varchar(50) DEFAULT NULL,
  `ContractDate` date DEFAULT NULL,
  `PRNo` varchar(50) DEFAULT NULL,
  `PRCreatedDate` date DEFAULT NULL,
  `PONo` varchar(50) DEFAULT NULL,
  `POCreatedDate` date DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `CapexNo` varchar(50) DEFAULT NULL,
  `CapexAmmount` double DEFAULT NULL,
  `CapexSpent` double DEFAULT NULL,
  `CapexBalance` double DEFAULT NULL,
  `RFCAmmount` double DEFAULT NULL,
  `Balance` double DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `Replacement` varchar(200) DEFAULT NULL,
  `ApprovedDoc` varchar(150) DEFAULT NULL,
  `RFQDoc` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `RFCNo` (`RFCNo`),
  UNIQUE KEY `RFQNo` (`RFQNo`),
  KEY `activity_id` (`activity_id`),
  KEY `contract_id` (`contract_id`),
  KEY `employee_id` (`employee_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `depthead` (`depthead`),
  KEY `contractor_id2` (`contractor_id2`),
  CONSTRAINT `FKRFCActivity` FOREIGN KEY (`activity_id`) REFERENCES `tbl_rfcactivity` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8857 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcactivity
CREATE TABLE IF NOT EXISTS `tbl_rfcactivity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ActivityCode` varchar(50) DEFAULT NULL,
  `ActivityDescr` varchar(200) DEFAULT NULL,
  `isHRRelated` bit(1) DEFAULT b'0',
  `isCapexRelated` bit(1) DEFAULT b'0',
  `Remarks` text DEFAULT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcapproval
CREATE TABLE IF NOT EXISTS `tbl_rfcapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfc_id_approver_id` (`rfc_id`,`approver_id`),
  KEY `approver_id` (`approver_id`),
  KEY `rfc_id` (`rfc_id`),
  CONSTRAINT `FKRFC` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKRFCApprover` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70286 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcattachment
CREATE TABLE IF NOT EXISTS `tbl_rfcattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(500) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `rfc_id` (`rfc_id`),
  CONSTRAINT `FKRFCAttachment` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19901 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfccontract
CREATE TABLE IF NOT EXISTS `tbl_rfccontract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ContractNo` varchar(50) NOT NULL,
  `ReleaseDate` date NOT NULL,
  `PeriodStart` date NOT NULL,
  `PeriodEnd` date NOT NULL,
  `DateSendToVendor` date NOT NULL,
  `DateSignByVendor` date NOT NULL,
  `DateReceivedFromVendor` date NOT NULL,
  `DateSignByDirectur` date NOT NULL,
  `DateChecklistDoneApproval` date NOT NULL,
  `Status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfccontractor
CREATE TABLE IF NOT EXISTS `tbl_rfccontractor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ContractorCode` varchar(50) NOT NULL,
  `ContractorName` varchar(100) NOT NULL,
  `isActive` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ContractorCode` (`ContractorCode`)
) ENGINE=InnoDB AUTO_INCREMENT=1393 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcdetail
CREATE TABLE IF NOT EXISTS `tbl_rfcdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rfc_id` (`rfc_id`),
  CONSTRAINT `FKRFCDetail` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15427 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfchistory
CREATE TABLE IF NOT EXISTS `tbl_rfchistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `approvaltype` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `remarks` text NOT NULL,
  `actiontype` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rfc_id` (`rfc_id`),
  CONSTRAINT `FKRFCHistory` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66864 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcprocremarks
CREATE TABLE IF NOT EXISTS `tbl_rfcprocremarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rfc_id` (`rfc_id`) USING BTREE,
  CONSTRAINT `tbl_rfcprocremarks_ibfk_1` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15429 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_rfcterm
CREATE TABLE IF NOT EXISTS `tbl_rfcterm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfc_id` int(11) NOT NULL,
  `Term` varchar(500) NOT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rfc_id` (`rfc_id`),
  CONSTRAINT `FKRFCTerm` FOREIGN KEY (`rfc_id`) REFERENCES `tbl_rfc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15718 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_role
CREATE TABLE IF NOT EXISTS `tbl_role` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RoleName` varchar(100) NOT NULL,
  `Created` datetime NOT NULL,
  `Modified` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_skrate
CREATE TABLE IF NOT EXISTS `tbl_skrate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SKNo` varchar(50) NOT NULL,
  `SKDescription` varchar(50) DEFAULT NULL,
  `CompanyCode` varchar(50) DEFAULT NULL,
  `ValidFrom` date NOT NULL,
  `ValidTo` date NOT NULL,
  `FuelRange` varchar(50) DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=388 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spkl
CREATE TABLE IF NOT EXISTS `tbl_spkl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SPKLNo` varchar(100) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `DateWork` date DEFAULT NULL,
  `RequestStatus` int(11) NOT NULL DEFAULT 0,
  `DeptHead` int(11) DEFAULT NULL,
  `ApprovedDoc` varchar(100) DEFAULT NULL,
  `TMSReqDate` datetime DEFAULT NULL,
  `TMSReqBy` int(11) DEFAULT NULL,
  `TMSReqStatus` int(11) NOT NULL DEFAULT 0,
  `Superior` int(11) DEFAULT NULL,
  `ApprovedTMSDoc` varchar(100) DEFAULT NULL,
  `isExceedPlan` bit(1) NOT NULL DEFAULT b'0',
  `ApprovalStep` int(11) NOT NULL DEFAULT 0,
  `isMoreThan2hours` bit(1) NOT NULL DEFAULT b'0',
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `OTReqBy` (`TMSReqBy`),
  KEY `Superior` (`Superior`)
) ENGINE=InnoDB AUTO_INCREMENT=5869 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spklapproval
CREATE TABLE IF NOT EXISTS `tbl_spklapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) DEFAULT 0,
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `spkl_id_approver_id` (`spkl_id`,`approver_id`),
  KEY `spkl_id` (`spkl_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `FKSKPLApproval` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKSKPLApprover` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35444 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spklattachment
CREATE TABLE IF NOT EXISTS `tbl_spklattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `file_descr` varchar(100) NOT NULL,
  `file_loc` varchar(500) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `spkl_id` (`spkl_id`),
  CONSTRAINT `FKSPKLAttachment` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19902 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spkldetail
CREATE TABLE IF NOT EXISTS `tbl_spkldetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `EstimateNormalHours` double NOT NULL,
  `EstimateOvertimeHours` double NOT NULL,
  `Target` varchar(400) NOT NULL,
  `ActualStartWork` datetime DEFAULT NULL,
  `ActualEndWork` datetime DEFAULT NULL,
  `ActualTotalHours` double NOT NULL,
  `ActualNormalHours` double NOT NULL,
  `ActualOvertimeHours` double NOT NULL,
  `DescriptionOfWork` varchar(400) NOT NULL,
  `isApproved` bit(1) DEFAULT NULL,
  `isOTApproved` bit(1) DEFAULT NULL,
  `Remarks` varchar(100) DEFAULT NULL,
  `RejectSPKLBy` int(11) DEFAULT NULL,
  `RejectOTBy` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spkl_id` (`spkl_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FKSPKLDetail` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14125 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spklhistory
CREATE TABLE IF NOT EXISTS `tbl_spklhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `approvaltype` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `remarks` text NOT NULL,
  `actiontype` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `spkl_id` (`spkl_id`),
  CONSTRAINT `FKSPKLHistory` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25834 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spklotapproval
CREATE TABLE IF NOT EXISTS `tbl_spklotapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` datetime DEFAULT NULL,
  `ApprovalStatus` int(11) DEFAULT 0,
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spkl_id` (`spkl_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `FKSKPLOTApproval` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKSKPLOTApprover` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1129 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spklothistory
CREATE TABLE IF NOT EXISTS `tbl_spklothistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spkl_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `approvaltype` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `remarks` text NOT NULL,
  `actiontype` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `spkl_id` (`spkl_id`),
  CONSTRAINT `FKSPKLOTHistory` FOREIGN KEY (`spkl_id`) REFERENCES `tbl_spkl` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3334 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_spkltimesheet
CREATE TABLE IF NOT EXISTS `tbl_spkltimesheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SourceData` varchar(50) NOT NULL,
  `DateWork` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `StartWork` time NOT NULL,
  `EndWork` time NOT NULL,
  `OvertimeHours` double NOT NULL,
  `BasicCalculation` double NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `DateWork_employee_id` (`DateWork`,`employee_id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_timesheet
CREATE TABLE IF NOT EXISTS `tbl_timesheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SourceData` varchar(50) DEFAULT NULL,
  `FingerPintID` int(11) NOT NULL,
  `SAPID` varchar(50) NOT NULL,
  `DateWork` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `StartWork` time DEFAULT NULL,
  `EndWork` time DEFAULT NULL,
  `TotalHours` double DEFAULT NULL,
  `NormalHours` double DEFAULT NULL,
  `OvertimeHours` double DEFAULT NULL,
  `BasicCalculation` double DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `DateWork_employee_id` (`DateWork`,`employee_id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `FKOTTimesheetEmployee` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee_02` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_tr
CREATE TABLE IF NOT EXISTS `tbl_tr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `RequestStatus` int(11) DEFAULT 0,
  `CreatedBy` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `TravelCategory` varchar(20) NOT NULL DEFAULT '',
  `isLandTransport` bit(1) NOT NULL DEFAULT b'0',
  `isAirTransport` bit(1) NOT NULL DEFAULT b'0',
  `isPersonalVehicle` bit(1) NOT NULL DEFAULT b'0',
  `isPoolCar` bit(1) NOT NULL DEFAULT b'0',
  `isDropOffOnly` bit(1) NOT NULL DEFAULT b'0',
  `isUntilJobFinish` bit(1) NOT NULL DEFAULT b'0',
  `JobFinishDate` date DEFAULT NULL,
  `isByTrain` bit(1) NOT NULL DEFAULT b'0',
  `isOther` bit(1) NOT NULL DEFAULT b'0',
  `OtherLandTransportDesc` varchar(250) NOT NULL,
  `isCommercialAirline` bit(1) NOT NULL DEFAULT b'0',
  `isCompanyAircraft` bit(1) NOT NULL DEFAULT b'0',
  `DeptHead` int(11) DEFAULT NULL,
  `Superior` int(11) DEFAULT NULL,
  `TravelCashAdvancePurpose` varchar(400) DEFAULT NULL,
  `SPPDDays` int(11) NOT NULL DEFAULT 0,
  `SPPDAmmount` double NOT NULL DEFAULT 0,
  `TaxiDays` int(11) NOT NULL DEFAULT 0,
  `TaxiAmmount` double NOT NULL DEFAULT 0,
  `AccommodationDays` int(11) NOT NULL DEFAULT 0,
  `AccommodationAmmount` double NOT NULL DEFAULT 0,
  `TelephoneDays` int(11) NOT NULL DEFAULT 0,
  `TelephoneAmmount` double NOT NULL DEFAULT 0,
  `OtherIDRDays` int(11) NOT NULL DEFAULT 0,
  `OtherIDRAmmount` double NOT NULL DEFAULT 0,
  `PerdiemAmmount` double NOT NULL DEFAULT 0,
  `OtherUSDAmmount` double NOT NULL DEFAULT 0,
  `TotalAdvanceIDR` double NOT NULL DEFAULT 0,
  `TotalAdvanceUSD` double NOT NULL DEFAULT 0,
  `ApprovedDoc` varchar(200) DEFAULT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `createdby` (`CreatedBy`),
  KEY `employee_id` (`employee_id`),
  KEY `DeptHead` (`DeptHead`),
  KEY `Superior` (`Superior`)
) ENGINE=InnoDB AUTO_INCREMENT=17642 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_trapproval
CREATE TABLE IF NOT EXISTS `tbl_trapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tr_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `ApprovalDate` date DEFAULT NULL,
  `ApprovalStatus` int(11) NOT NULL,
  `Remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tr_id_approver_id` (`tr_id`,`approver_id`),
  KEY `request_id` (`tr_id`),
  KEY `employee_id` (`approver_id`),
  CONSTRAINT `FKTRApp` FOREIGN KEY (`tr_id`) REFERENCES `tbl_tr` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FKTREmpApp` FOREIGN KEY (`approver_id`) REFERENCES `tbl_approver` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63521 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_trattachment
CREATE TABLE IF NOT EXISTS `tbl_trattachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tr_id` int(11) NOT NULL,
  `file_descr` varchar(255) NOT NULL,
  `file_loc` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `FK_tbl_advanceattachment_tbl_advance` (`tr_id`),
  CONSTRAINT `FK_tbl_trattachment_tbl_tr` FOREIGN KEY (`tr_id`) REFERENCES `tbl_tr` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14220 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_trhistory
CREATE TABLE IF NOT EXISTS `tbl_trhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tr_id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `approvaltype` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `actiontype` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tr_id` (`tr_id`),
  CONSTRAINT `FKTR` FOREIGN KEY (`tr_id`) REFERENCES `tbl_tr` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89153 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_trschedule
CREATE TABLE IF NOT EXISTS `tbl_trschedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tr_id` int(11) NOT NULL,
  `DepartDate` date DEFAULT NULL,
  `DepartTime` time DEFAULT NULL,
  `DepartFrom` varchar(100) DEFAULT NULL,
  `ArrivingDate` date DEFAULT NULL,
  `ArrivingTime` time DEFAULT NULL,
  `ArrivingTo` varchar(100) DEFAULT NULL,
  `Region` varchar(2) DEFAULT 'R1',
  `Reason` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tr_id` (`tr_id`),
  CONSTRAINT `FKTrSchedule` FOREIGN KEY (`tr_id`) REFERENCES `tbl_tr` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28614 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_trticket
CREATE TABLE IF NOT EXISTS `tbl_trticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tr_id` int(11) NOT NULL,
  `TicketFor` varchar(100) DEFAULT NULL,
  `TicketName` varchar(100) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `PhoneNumber` varchar(100) DEFAULT NULL,
  `Gender` varchar(50) DEFAULT NULL,
  `HRRemarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tr_id` (`tr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44458 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_unit
CREATE TABLE IF NOT EXISTS `tbl_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `detail` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_user
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Role_id` int(11) NOT NULL,
  `isAdmin` bit(1) NOT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserRole` (`Role_id`),
  CONSTRAINT `FKRole` FOREIGN KEY (`Role_id`) REFERENCES `tbl_role` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_useraccess
CREATE TABLE IF NOT EXISTS `tbl_useraccess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `allowAdd` bit(1) DEFAULT b'0',
  `allowEdit` bit(1) DEFAULT b'0',
  `allowDelete` bit(1) DEFAULT b'0',
  `allowView` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqempmodule` (`module_id`,`employee_id`),
  KEY `module_id` (`module_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `FKAccessModule` FOREIGN KEY (`module_id`) REFERENCES `tbl_module` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog
CREATE TABLE IF NOT EXISTS `tbl_userlog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) NOT NULL,
  `displayname` varchar(50) NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`),
  KEY `LastAccess` (`LastAccess`),
  KEY `ldap_userid` (`ldap_userid`),
  KEY `LoginIP` (`LoginIP`),
  KEY `isActive` (`isActive`),
  CONSTRAINT `FKUser` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=454691 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog012022
CREATE TABLE IF NOT EXISTS `tbl_userlog012022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog022022
CREATE TABLE IF NOT EXISTS `tbl_userlog022022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog032022
CREATE TABLE IF NOT EXISTS `tbl_userlog032022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog042022
CREATE TABLE IF NOT EXISTS `tbl_userlog042022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog052022
CREATE TABLE IF NOT EXISTS `tbl_userlog052022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog062022
CREATE TABLE IF NOT EXISTS `tbl_userlog062022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog072022
CREATE TABLE IF NOT EXISTS `tbl_userlog072022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog082022
CREATE TABLE IF NOT EXISTS `tbl_userlog082022` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog2020
CREATE TABLE IF NOT EXISTS `tbl_userlog2020` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog2021
CREATE TABLE IF NOT EXISTS `tbl_userlog2021` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog2023
CREATE TABLE IF NOT EXISTS `tbl_userlog2023` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table oasys_dev.tbl_userlog2024
CREATE TABLE IF NOT EXISTS `tbl_userlog2024` (
  `ID` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `ldap_userid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LoginTime` datetime NOT NULL,
  `LoginIP` varchar(50) CHARACTER SET latin1 NOT NULL,
  `LastAccess` datetime NOT NULL,
  `isActive` bit(1) NOT NULL,
  `LoginApp` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for view oasys_dev.vwaddressbook
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwaddressbook` (
	`ID` INT(11) NOT NULL,
	`username` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`fullname` VARCHAR(150) NOT NULL COLLATE 'latin1_swedish_ci',
	`email` VARCHAR(150) NOT NULL COLLATE 'latin1_swedish_ci',
	`company` VARCHAR(150) NOT NULL COLLATE 'latin1_swedish_ci',
	`phone` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`type` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`active` BIT(1) NOT NULL,
	`remarks` VARCHAR(200) NOT NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovebufc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovebufc` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovebuhead` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovedepmd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovedepmd` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovedepthead` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovefinance
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovefinance` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovehrdverifikator
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovehrdverifikator` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapprovemd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapprovemd` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvanceapproveproc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvanceapproveproc` (
	`id` INT(11) NOT NULL,
	`advance_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvancereport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvancereport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdverifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprProcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcDate` DATETIME NULL,
	`ApprBufcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprDepmdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDepmdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDepmdDate` DATETIME NULL,
	`ApprMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdDate` DATETIME NULL,
	`ApprFinanceName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFinanceStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFinanceDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(34) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvancereportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvancereportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdverifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprProcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcDate` DATETIME NULL,
	`ApprBufcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprDepmdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDepmdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDepmdDate` DATETIME NULL,
	`ApprMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdDate` DATETIME NULL,
	`ApprFinanceName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFinanceStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFinanceDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpenseapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`advexpense_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovehrdverifikator
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpenseapprovehrdverifikator` (
	`id` INT(11) NOT NULL,
	`advexpense_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovesuperior
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpenseapprovesuperior` (
	`id` INT(11) NOT NULL,
	`advexpense_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpensedetailbtcheck
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpensedetailbtcheck` (
	`advexpense_id` INT(11) NOT NULL,
	`departdate` DATE NULL,
	`departtime` TIME NULL,
	`returndate` DATE NULL,
	`returntime` TIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpenseforsap
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpenseforsap` (
	`createddate` DATETIME NULL,
	`companycode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`companyname` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`requeststatus` INT(11) NULL,
	`expenseno` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`paymenttype` INT(2) NULL,
	`advanceno` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`lessadvance` INT(11) NULL,
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`email` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`costcenter` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`bg` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`startdate` DATE NULL,
	`enddate` DATE NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`HeaderRemarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DeptHeadEmail` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`ExpenseType` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`Purpose` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`ReceiptDate` DATE NULL,
	`Amount` DOUBLE NULL,
	`Currency` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ExchangeRate` INT(11) NULL,
	`PaymentAmount` INT(11) NULL,
	`CostCentre` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Country` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Location` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`DetailRemarks` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpensereport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpensereport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Superior` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprSuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorDate` DATETIME NULL,
	`ApprHrdVerifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvexpensereportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvexpensereportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Superior` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprSuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorDate` DATETIME NULL,
	`ApprHrdVerifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovebufc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentapprovebufc` (
	`id` INT(11) NOT NULL,
	`advpayment_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentapprovebuhead` (
	`id` INT(11) NOT NULL,
	`advpayment_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentapprovedepthead` (
	`id` INT(11) NOT NULL,
	`advpayment_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`advpayment_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovehrdverifikator
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentapprovehrdverifikator` (
	`id` INT(11) NOT NULL,
	`advpayment_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentforsap
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentforsap` (
	`id` INT(11) NULL,
	`CreatedDate` DATETIME NULL,
	`PaymentNo` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`PaymentForm` INT(11) NULL,
	`PaymentType` INT(2) NULL,
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`Name` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Email` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NULL,
	`AdvanceNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`LessAdvance` INT(11) NULL,
	`Payment` INT(11) NULL,
	`Beneficiary` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`AccountName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Bank` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`AccountNumber` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`NoVoucher` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`DueDate` DATE NULL,
	`PaymentDate` DATE NULL,
	`Reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`HeaderRemarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHeadSAP_ID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`DeptHead` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DeptHeadEmail` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`Description` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`AccountCode` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Amount` BIGINT(20) NULL,
	`DetailRemarks` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentreport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdverifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdverifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBufcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwadvpaymentreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwadvpaymentreportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdVerifikatorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdVerifikatorDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBufcName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBufcDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwallpending
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwallpending` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`Reqby` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`company_id` INT(11) NULL,
	`companyname` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`requeststatus` INT(11) NULL,
	`sequence` INT(11) NULL,
	`FullName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwcontract
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwcontract` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`ContractNo` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_general_ci',
	`contractor_id` INT(11) NULL,
	`ContractorName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`PeriodStart` DATE NOT NULL,
	`PeriodEnd` DATE NOT NULL,
	`Description` TEXT NULL COLLATE 'utf8mb4_general_ci',
	`ContractStatus` INT(11) NOT NULL,
	`OldContractNo` INT(11) NULL,
	`NewContractNo` INT(11) NULL,
	`ContractDocument` VARCHAR(400) NULL COLLATE 'utf8mb4_general_ci',
	`CreatedBy` INT(11) NULL,
	`CreatedDate` DATETIME NULL,
	`ModifiedBy` INT(11) NULL,
	`ModifiedDate` DATETIME NULL,
	`ActivityDescr` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`RFCNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RateType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SKNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`CompanyCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RFCUser` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`RFCUserEmail` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`RFCDeptHeadEmail` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`isActive` BIT(1) NOT NULL,
	`isDeleted` BIT(1) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffapprovebuhead` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffapprovedepthead` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffapprovedeputymd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffapprovedeputymd` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffapprovehrd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffapprovehrd` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffapprovesuperior
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffapprovesuperior` (
	`id` INT(11) NOT NULL,
	`dayoff_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffreport` (
	`id` INT(11) NOT NULL,
	`RequestDate` DATE NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NULL,
	`Remarks` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`Superior` INT(11) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`ApprSuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorDate` DATE NULL,
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATE NULL,
	`ApprBuHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuHeadDate` DATE NULL,
	`ApprDeputyMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeputyMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeputyMdDate` DATE NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATE NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffreportsubquery` (
	`id` INT(11) NOT NULL,
	`RequestDate` DATE NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NULL,
	`Remarks` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`Superior` INT(11) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`ApprSuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorDate` DATE NULL,
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATE NULL,
	`ApprBuHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuHeadDate` DATE NULL,
	`ApprDeputyMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeputyMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeputyMdDate` DATE NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATE NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwdayoffreq
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwdayoffreq` (
	`id` INT(11) NOT NULL,
	`RequestDate` DATE NULL,
	`RequestStatus` INT(11) NULL,
	`ReqStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`employee_id` INT(11) NOT NULL,
	`FullName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`superior` INT(11) NULL,
	`SuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`depthead` INT(11) NULL,
	`DeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`companyname` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`DepartmentName` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`ApprovedDoc` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Remarks` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwemptemplate
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwemptemplate` (
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`Name` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`PA` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PAText` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`SubArea` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SubAreaText` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostCenter` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`OU` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OUText` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`Position` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PositionText` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Level` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MappingLevel` CHAR NOT NULL COLLATE 'utf8mb4_general_ci',
	`Mapping` CHAR NOT NULL COLLATE 'utf8mb4_general_ci',
	`LoginName` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`OldSAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`companycode` VARCHAR(10) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwexporthc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwexporthc` (
	`no` CHAR NOT NULL COLLATE 'utf8mb4_general_ci',
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`NAME` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`PA` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PAText` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`SubArea` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SubAreaText` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostCenter` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`OU` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OUText` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`position` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PositionText` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Grade` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MappingLevel` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Mapping` CHAR NOT NULL COLLATE 'utf8mb4_general_ci',
	`UserName` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`OldSAP` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`CompanyCodeUsed` VARCHAR(10) NULL COLLATE 'utf8mb4_general_ci',
	`JoinDate` DATE NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteieapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteieapprovebuhead` (
	`id` INT(11) NOT NULL,
	`iteie_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteieapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteieapprovedepthead` (
	`id` INT(11) NOT NULL,
	`iteie_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteieapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteieapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`iteie_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteieapproveitlead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteieapproveitlead` (
	`id` INT(11) NOT NULL,
	`iteie_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteiereport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteiereport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Name` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`EmployeeID` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DepartmentUser` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isVip` BIT(1) NULL,
	`AccessType` INT(11) NULL,
	`AccountType` INT(11) NULL,
	`ValidFrom` DATE NULL,
	`ValidTo` DATE NULL,
	`ListGroup` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwiteiereportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwiteiereportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Name` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`EmployeeID` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DepartmentUser` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isVip` BIT(1) NULL,
	`AccessType` INT(11) NULL,
	`AccountType` INT(11) NULL,
	`ValidFrom` DATE NULL,
	`ValidTo` DATE NULL,
	`ListGroup` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailapprovebuhead` (
	`id` INT(11) NOT NULL,
	`itimail_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailapprovedepthead` (
	`id` INT(11) NOT NULL,
	`itimail_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`itimail_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailapproveitlead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailapproveitlead` (
	`id` INT(11) NOT NULL,
	`itimail_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailapprovemd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailapprovemd` (
	`id` INT(11) NOT NULL,
	`itimail_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailreport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`FormType` INT(11) NULL,
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`AccessRequested` INT(11) NULL,
	`AccessType` INT(11) NULL,
	`AccountType` INT(11) NULL,
	`EmailQuota` INT(11) NULL,
	`ValidFrom` DATE NULL,
	`ValidTo` DATE NULL,
	`EmailDomain` INT(11) NULL,
	`ListGroup` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ListGroupModeration` INT(11) NULL,
	`web1` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`web2` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`NewMailBoxSize` INT(11) NULL,
	`IncomingSize` INT(11) NULL,
	`OutgoingSize` INT(11) NULL,
	`TypeOfAccess` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`EmailGroupName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`MemberName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`isDeclaration` BIT(1) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitimailreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitimailreportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`FormType` INT(11) NULL,
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`AccessRequested` INT(11) NULL,
	`AccessType` INT(11) NULL,
	`AccountType` INT(11) NULL,
	`EmailQuota` INT(11) NULL,
	`ValidFrom` DATE NULL,
	`ValidTo` DATE NULL,
	`EmailDomain` INT(11) NULL,
	`ListGroup` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ListGroupModeration` INT(11) NULL,
	`web1` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`web2` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`NewMailBoxSize` INT(11) NULL,
	`IncomingSize` INT(11) NULL,
	`OutgoingSize` INT(11) NULL,
	`TypeOfAccess` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`EmailGroupName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`MemberName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`isDeclaration` BIT(1) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprMdName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMdDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefapprovebuhead` (
	`id` INT(11) NOT NULL,
	`itsharef_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefapprovedepthead` (
	`id` INT(11) NOT NULL,
	`itsharef_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefapprovehrdhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefapprovehrdhead` (
	`id` INT(11) NOT NULL,
	`itsharef_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefapproveitlead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefapproveitlead` (
	`id` INT(11) NOT NULL,
	`itsharef_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefapproveitsite
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefapproveitsite` (
	`id` INT(11) NOT NULL,
	`itsharef_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefreport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Grade` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestType` INT(11) NOT NULL,
	`FolderName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`AccountType` INT(11) NULL,
	`ValidFrom` DATETIME NULL,
	`ValidTo` DATETIME NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`isDeclaration` BIT(1) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprItsiteName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItsiteStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItsiteDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`DepartmentR` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwitsharefreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwitsharefreportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`Designation` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`Grade` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`BgBu` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OfficeLocation` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Floor` INT(11) NULL,
	`PhoneExt` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Department` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequestType` INT(11) NOT NULL,
	`FolderName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`AccountType` INT(11) NULL,
	`ValidFrom` DATETIME NULL,
	`ValidTo` DATETIME NULL,
	`reason` TEXT NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`isDeclaration` BIT(1) NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprHrdHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHrdHeadDate` DATETIME NULL,
	`ApprBuheadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuheadDate` DATETIME NULL,
	`ApprItsiteName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItsiteStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItsiteDate` DATETIME NULL,
	`ApprItleadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprItleadDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf28approvebuyer
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf28approvebuyer` (
	`id` INT(11) NOT NULL,
	`mmf28_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf28approvedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf28approvedepthead` (
	`id` INT(11) NOT NULL,
	`mmf28_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf28approveprochead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf28approveprochead` (
	`id` INT(11) NOT NULL,
	`mmf28_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf28report
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf28report` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`WONumber` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`TelpNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ChargeCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialDispatch` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequiredDate` DATETIME NULL,
	`MaterialCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialDescr` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Symptomps` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`RequiredType` INT(11) NOT NULL,
	`RequiredOther` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Instruction` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`isHazardousChemical` BIT(1) NOT NULL,
	`HazChemicalName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isDecontaminated` BIT(1) NOT NULL,
	`isNotContaminated` BIT(1) NOT NULL,
	`NotContaminatedReason` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isNonHazardous` BIT(1) NOT NULL,
	`NonHazChemicalName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isNonChemical` BIT(1) NOT NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`MaterialDispatchNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`isRepair` BIT(1) NOT NULL,
	`isScrap` BIT(1) NOT NULL,
	`EstimateCost` DOUBLE NULL,
	`PONo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialReturnedDate` DATETIME NULL,
	`SupplierDODNNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`Buyer` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprProcHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadDate` DATETIME NULL,
	`ApprBuyerName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Department` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(33) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf28reportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf28reportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`WONumber` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MMFNumber` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`RequestStatus` INT(11) NOT NULL,
	`TelpNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ChargeCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialDispatch` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`RequiredDate` DATETIME NULL,
	`MaterialCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialDescr` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Symptomps` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`RequiredType` INT(11) NOT NULL,
	`RequiredOther` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Instruction` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`isHazardousChemical` BIT(1) NOT NULL,
	`HazChemicalName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isDecontaminated` BIT(1) NOT NULL,
	`isNotContaminated` BIT(1) NOT NULL,
	`NotContaminatedReason` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isNonHazardous` BIT(1) NOT NULL,
	`NonHazChemicalName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isNonChemical` BIT(1) NOT NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`MaterialDispatchNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`isRepair` BIT(1) NOT NULL,
	`isScrap` BIT(1) NOT NULL,
	`EstimateCost` DOUBLE NULL,
	`PONo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`MaterialReturnedDate` DATETIME NULL,
	`SupplierDODNNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`action` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`Buyer` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprProcHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadDate` DATETIME NULL,
	`ApprBuyerName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf30approvebuyer
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf30approvebuyer` (
	`id` INT(11) NOT NULL,
	`mmf30_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`ProcComments` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf30approvedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf30approvedepthead` (
	`id` INT(11) NOT NULL,
	`mmf30_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`ProcComments` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf30approveprochead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf30approveprochead` (
	`id` INT(11) NOT NULL,
	`mmf30_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`ProcComments` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf30report
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf30report` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`PRType` INT(11) NOT NULL,
	`RequisitionType` INT(11) NOT NULL,
	`RequisitionOther` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PRNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DeliverTo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostElement` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierAddress` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierEmailFax` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ReceivedOn` DATE NULL,
	`ReceivedByName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`SupplierContractNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Reason` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`RemarksU` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`ProcComments` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`Buyer` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprProcHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadDate` DATETIME NULL,
	`ApprBuyerName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Department` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(33) NOT NULL COLLATE 'utf8mb4_general_ci',
	`ApprStatusCode` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwmmf30reportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwmmf30reportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`RequestStatus` INT(11) NOT NULL,
	`PRType` INT(11) NOT NULL,
	`RequisitionType` INT(11) NOT NULL,
	`RequisitionOther` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PRNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`DeliverTo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostCode` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CostElement` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierName` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierAddress` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`SupplierEmailFax` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ReceivedOn` DATE NULL,
	`ReceivedByName` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`SupplierContractNo` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`Reason` VARCHAR(300) NULL COLLATE 'latin1_swedish_ci',
	`RemarksU` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`ProcComments` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`DeptHead` INT(11) NULL,
	`Buyer` INT(11) NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprProcHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprProcHeadDate` DATETIME NULL,
	`ApprBuyerName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBuyerDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwnewemp
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwnewemp` (
	`id` INT(11) NOT NULL,
	`company_id` INT(11) NULL,
	`department_id` INT(11) NULL,
	`division_id` INT(11) NULL,
	`designation_id` INT(11) NULL,
	`grade_id` INT(11) NULL,
	`level_id` INT(11) NULL,
	`LoginName` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`SAPID` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`JoinDate` DATE NULL,
	`location_id` INT(11) NULL,
	`FullName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Address` VARCHAR(200) NULL COLLATE 'utf8mb4_general_ci',
	`Gender` VARCHAR(20) NULL COLLATE 'utf8mb4_general_ci',
	`religion_id` INT(11) NULL,
	`MaritalStatus` VARCHAR(10) NULL COLLATE 'utf8mb4_general_ci',
	`isActive` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`isInternationalStaff` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`companycode` VARCHAR(10) NULL COLLATE 'utf8mb4_general_ci',
	`Remarks` VARCHAR(255) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwoasysappr
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwoasysappr` (
	`row_num` BIGINT(21) NOT NULL,
	`sistem` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`employee_id` INT(11) NOT NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`module` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`role` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`active` BIT(1) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwpendingapproval
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwpendingapproval` (
	`module` VARCHAR(7) NOT NULL COLLATE 'utf8mb4_general_ci',
	`xid` INT(11) NULL,
	`employee_id` INT(11) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwpendingbyemp
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwpendingbyemp` (
	`module` VARCHAR(7) NOT NULL COLLATE 'utf8mb4_general_ci',
	`employee_id` INT(11) NULL,
	`jml` BIGINT(21) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwpendingreq
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwpendingreq` (
	`module` VARCHAR(7) NOT NULL COLLATE 'utf8mb4_general_ci',
	`employee_id` INT(11) NOT NULL,
	`jml` BIGINT(21) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovebufc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovebufc` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovebuhead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovebuhead` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovecadbu
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovecadbu` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovecadkf
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovecadkf` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovecpu
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovecpu` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovedepthead` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovehrd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovehrd` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovehrkf
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovehrkf` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovekffc
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovekffc` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcapprovemdkf
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcapprovemdkf` (
	`id` INT(11) NOT NULL,
	`rfc_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcreport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`CompanyCode` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`depthead` INT(11) NULL,
	`RFCNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`activity_id` INT(11) NULL,
	`RFCType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isProjectCapex` BIT(1) NULL,
	`OldContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PeriodStart` DATE NULL,
	`PeriodEnd` DATE NULL,
	`RateType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Rate` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`contractor_id` INT(11) NULL,
	`contractor_id2` INT(11) NULL,
	`PaymentTerm` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractDate` DATE NULL,
	`PRNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PRCreatedDate` DATE NULL,
	`PONo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`POCreatedDate` DATE NULL,
	`contract_id` INT(11) NULL,
	`RequestStatus` INT(11) NULL,
	`CapexNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CapexAmmount` DOUBLE NULL,
	`CapexSpent` DOUBLE NULL,
	`CapexBalance` DOUBLE NULL,
	`RFCAmmount` DOUBLE NULL,
	`Balance` DOUBLE NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`ApprovedDoc` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprCADBUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADBUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADBUDate` DATETIME NULL,
	`ApprHRDName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDDate` DATETIME NULL,
	`ApprHRKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFDate` DATETIME NULL,
	`ApprCADKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADKFDate` DATETIME NULL,
	`ApprFCBUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFCBUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFCBUDate` DATETIME NULL,
	`ApprBUHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBUHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBUHeadDate` DATETIME NULL,
	`ApprCPUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCPUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCPUDate` DATETIME NULL,
	`ApprKFFCName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprKFFCStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprKFFCDate` DATETIME NULL,
	`ApprMDKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFDate` DATETIME NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Department` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`KindOfContract` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`ContractorRecom1` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`ContractorRecom2` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwrfcreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwrfcreportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`CompanyCode` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`depthead` INT(11) NULL,
	`RFCNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`activity_id` INT(11) NULL,
	`RFCType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`isProjectCapex` BIT(1) NULL,
	`OldContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PeriodStart` DATE NULL,
	`PeriodEnd` DATE NULL,
	`RateType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`Rate` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`contractor_id` INT(11) NULL,
	`contractor_id2` INT(11) NULL,
	`PaymentTerm` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`ContractDate` DATE NULL,
	`PRNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`PRCreatedDate` DATE NULL,
	`PONo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`POCreatedDate` DATE NULL,
	`contract_id` INT(11) NULL,
	`RequestStatus` INT(11) NULL,
	`CapexNo` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`CapexAmmount` DOUBLE NULL,
	`CapexSpent` DOUBLE NULL,
	`CapexBalance` DOUBLE NULL,
	`RFCAmmount` DOUBLE NULL,
	`Balance` DOUBLE NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`ApprovedDoc` VARCHAR(150) NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATETIME NULL,
	`ApprCADBUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADBUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADBUDate` DATETIME NULL,
	`ApprHRDName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDDate` DATETIME NULL,
	`ApprHRKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFDate` DATETIME NULL,
	`ApprCADKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCADKFDate` DATETIME NULL,
	`ApprFCBUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFCBUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprFCBUDate` DATETIME NULL,
	`ApprBUHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBUHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprBUHeadDate` DATETIME NULL,
	`ApprCPUName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCPUStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprCPUDate` DATETIME NULL,
	`ApprKFFCName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprKFFCStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprKFFCDate` DATETIME NULL,
	`ApprMDKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklapppending
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklapppending` (
	`id` INT(11) NOT NULL,
	`spkl_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`ApprovalType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklapproval
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklapproval` (
	`id` INT(11) NOT NULL,
	`spkl_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`ApprovalType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklapprovalsub
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklapprovalsub` (
	`sequence` INT(11) NULL,
	`spkl_id` INT(11) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklotapppending
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklotapppending` (
	`id` INT(11) NOT NULL,
	`spkl_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`ApprovalType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklotapproval
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklotapproval` (
	`id` INT(11) NOT NULL,
	`spkl_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATETIME NULL,
	`ApprovalStatus` INT(11) NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`ApprovalType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklotapprovalsub
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklotapprovalsub` (
	`sequence` INT(11) NULL,
	`spkl_id` INT(11) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwspklreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwspklreport` (
	`id` INT(11) NOT NULL,
	`SPKLNo` VARCHAR(100) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NULL,
	`DateWork` DATE NULL,
	`RequestStatus` INT(11) NOT NULL,
	`DeptHead` INT(11) NULL,
	`ApprovedDoc` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`TMSReqDate` DATETIME NULL,
	`TMSReqBy` INT(11) NULL,
	`TMSReqStatus` INT(11) NOT NULL,
	`Superior` INT(11) NULL,
	`ApprovedTMSDoc` VARCHAR(100) NULL COLLATE 'latin1_swedish_ci',
	`isExceedPlan` BIT(1) NOT NULL,
	`ApprovalStep` INT(11) NOT NULL,
	`isMoreThan2hours` BIT(1) NOT NULL,
	`Remarks` TEXT NULL COLLATE 'latin1_swedish_ci',
	`SPKLStatus` VARCHAR(67) NULL COLLATE 'latin1_swedish_ci',
	`OTStatus` VARCHAR(67) NULL COLLATE 'latin1_swedish_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`SPKLApprName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`SPKLApprDept` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`SPKLApprType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci',
	`OTApprName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`OTApprDept` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`OTApprType` VARCHAR(50) NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrapprovedepthead
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrapprovedepthead` (
	`id` INT(11) NOT NULL,
	`tr_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrapprovehrbu
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrapprovehrbu` (
	`id` INT(11) NOT NULL,
	`tr_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrapprovehrho
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrapprovehrho` (
	`id` INT(11) NOT NULL,
	`tr_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrapprovemd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrapprovemd` (
	`id` INT(11) NOT NULL,
	`tr_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrapprovesuperior
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrapprovesuperior` (
	`id` INT(11) NOT NULL,
	`tr_id` INT(11) NOT NULL,
	`approver_id` INT(11) NOT NULL,
	`ApprovalDate` DATE NULL,
	`ApprovalStatus` INT(11) NOT NULL,
	`Remarks` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`employee_id` INT(11) NULL,
	`fullname` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`departmentname` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`approvaltype_id` INT(11) NULL,
	`sequence` INT(11) NULL,
	`isFinal` BIT(1) NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrreport
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrreport` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`CreatedBy` INT(11) NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`TravelCategory` VARCHAR(20) NOT NULL COLLATE 'latin1_swedish_ci',
	`isLandTransport` BIT(1) NOT NULL,
	`isAirTransport` BIT(1) NOT NULL,
	`isPersonalVehicle` BIT(1) NOT NULL,
	`isPoolCar` BIT(1) NOT NULL,
	`isDropOffOnly` BIT(1) NOT NULL,
	`isUntilJobFinish` BIT(1) NOT NULL,
	`JobFinishDate` DATE NULL,
	`isByTrain` BIT(1) NOT NULL,
	`isOther` BIT(1) NOT NULL,
	`OtherLandTransportDesc` VARCHAR(250) NOT NULL COLLATE 'latin1_swedish_ci',
	`isCommercialAirline` BIT(1) NOT NULL,
	`isCompanyAircraft` BIT(1) NOT NULL,
	`DeptHead` INT(11) NULL,
	`RequestStatus` INT(11) NULL,
	`TravelCashAdvancePurpose` VARCHAR(400) NULL COLLATE 'latin1_swedish_ci',
	`SPPDDays` INT(11) NOT NULL,
	`SPPDAmmount` DOUBLE NOT NULL,
	`TaxiDays` INT(11) NOT NULL,
	`TaxiAmmount` DOUBLE NOT NULL,
	`AccommodationDays` INT(11) NOT NULL,
	`AccommodationAmmount` DOUBLE NOT NULL,
	`TelephoneDays` INT(11) NOT NULL,
	`TelephoneAmmount` DOUBLE NOT NULL,
	`OtherIDRDays` INT(11) NOT NULL,
	`OtherIDRAmmount` DOUBLE NOT NULL,
	`PerdiemAmmount` DOUBLE NOT NULL,
	`OtherUSDAmmount` DOUBLE NOT NULL,
	`TotalAdvanceIDR` DOUBLE NOT NULL,
	`TotalAdvanceUSD` DOUBLE NOT NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATE NULL,
	`ApprHRDName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDDate` DATE NULL,
	`ApprHRKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFDate` DATE NULL,
	`ApprMDKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFDate` DATE NULL,
	`RequestBy` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`Department` VARCHAR(250) NULL COLLATE 'latin1_swedish_ci',
	`LastStatus` VARCHAR(26) NULL COLLATE 'utf8mb4_general_ci',
	`PersonHolding` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwtrreportsubquery
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwtrreportsubquery` (
	`id` INT(11) NOT NULL,
	`CreatedDate` DATETIME NOT NULL,
	`CreatedBy` INT(11) NOT NULL,
	`employee_id` INT(11) NOT NULL,
	`TravelCategory` VARCHAR(20) NOT NULL COLLATE 'latin1_swedish_ci',
	`isLandTransport` BIT(1) NOT NULL,
	`isAirTransport` BIT(1) NOT NULL,
	`isPersonalVehicle` BIT(1) NOT NULL,
	`isPoolCar` BIT(1) NOT NULL,
	`isDropOffOnly` BIT(1) NOT NULL,
	`isUntilJobFinish` BIT(1) NOT NULL,
	`JobFinishDate` DATE NULL,
	`isByTrain` BIT(1) NOT NULL,
	`isOther` BIT(1) NOT NULL,
	`OtherLandTransportDesc` VARCHAR(250) NOT NULL COLLATE 'latin1_swedish_ci',
	`isCommercialAirline` BIT(1) NOT NULL,
	`isCompanyAircraft` BIT(1) NOT NULL,
	`DeptHead` INT(11) NULL,
	`RequestStatus` INT(11) NULL,
	`TravelCashAdvancePurpose` VARCHAR(400) NULL COLLATE 'latin1_swedish_ci',
	`SPPDDays` INT(11) NOT NULL,
	`SPPDAmmount` DOUBLE NOT NULL,
	`TaxiDays` INT(11) NOT NULL,
	`TaxiAmmount` DOUBLE NOT NULL,
	`AccommodationDays` INT(11) NOT NULL,
	`AccommodationAmmount` DOUBLE NOT NULL,
	`TelephoneDays` INT(11) NOT NULL,
	`TelephoneAmmount` DOUBLE NOT NULL,
	`OtherIDRDays` INT(11) NOT NULL,
	`OtherIDRAmmount` DOUBLE NOT NULL,
	`PerdiemAmmount` DOUBLE NOT NULL,
	`OtherUSDAmmount` DOUBLE NOT NULL,
	`TotalAdvanceIDR` DOUBLE NOT NULL,
	`TotalAdvanceUSD` DOUBLE NOT NULL,
	`ApprovedDoc` VARCHAR(200) NULL COLLATE 'latin1_swedish_ci',
	`Remarks` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`ApprSuperiorName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprSuperiorDate` DATE NULL,
	`ApprDeptHeadName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprDeptHeadDate` DATE NULL,
	`ApprHRDName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRDDate` DATE NULL,
	`ApprHRKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprHRKFDate` DATE NULL,
	`ApprMDKFName` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFStatus` VARCHAR(16) NULL COLLATE 'utf8mb4_general_ci',
	`ApprMDKFDate` DATE NULL
) ENGINE=MyISAM;

-- Dumping structure for view oasys_dev.vwaddressbook
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwaddressbook`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwaddressbook` AS select `ta`.`ID` AS `ID`,`ta`.`username` AS `username`,`ta`.`fullname` AS `fullname`,`ta`.`email` AS `email`,`ta`.`company` AS `company`,`ta`.`phone` AS `phone`,`ta`.`type` AS `type`,`ta`.`active` AS `active`,`ta`.`remarks` AS `remarks` from (`tbl_addressbook` `ta` left join `tbl_employee` `te` on(`ta`.`username` = `te`.`LoginName`)) where `ta`.`type` = 'User' and `ta`.`email` is not null and `ta`.`active` = '1' and `te`.`isActive` = '1' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovebufc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovebufc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovebufc` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '37' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovebuhead` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '38' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovedepmd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovedepmd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovedepmd` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '39' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovedepthead` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '35' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovefinance
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovefinance`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovefinance` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '41' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '36' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovehrdverifikator
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovehrdverifikator`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovehrdverifikator` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '44' ;

-- Dumping structure for view oasys_dev.vwadvanceapprovemd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapprovemd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapprovemd` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '40' ;

-- Dumping structure for view oasys_dev.vwadvanceapproveproc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvanceapproveproc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvanceapproveproc` AS select `a`.`id` AS `id`,`a`.`advance_id` AS `advance_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advanceapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '42' ;

-- Dumping structure for view oasys_dev.vwadvancereport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvancereport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvancereport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHrdverifikatorName` AS `ApprHrdverifikatorName`,`r`.`ApprHrdverifikatorStatus` AS `ApprHrdverifikatorStatus`,`r`.`ApprHrdverifikatorDate` AS `ApprHrdverifikatorDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`r`.`ApprProcName` AS `ApprProcName`,`r`.`ApprProcStatus` AS `ApprProcStatus`,`r`.`ApprProcDate` AS `ApprProcDate`,`r`.`ApprBufcName` AS `ApprBufcName`,`r`.`ApprBufcStatus` AS `ApprBufcStatus`,`r`.`ApprBufcDate` AS `ApprBufcDate`,`r`.`ApprBuheadName` AS `ApprBuheadName`,`r`.`ApprBuheadStatus` AS `ApprBuheadStatus`,`r`.`ApprBuheadDate` AS `ApprBuheadDate`,`r`.`ApprDepmdName` AS `ApprDepmdName`,`r`.`ApprDepmdStatus` AS `ApprDepmdStatus`,`r`.`ApprDepmdDate` AS `ApprDepmdDate`,`r`.`ApprMdName` AS `ApprMdName`,`r`.`ApprMdStatus` AS `ApprMdStatus`,`r`.`ApprMdDate` AS `ApprMdDate`,`r`.`ApprFinanceName` AS `ApprFinanceName`,`r`.`ApprFinanceStatus` AS `ApprFinanceStatus`,`r`.`ApprFinanceDate` AS `ApprFinanceDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHrdverifikatorDate` is null and `r`.`ApprHrdverifikatorName` is not null then ' HRD Verifikator' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' when `r`.`ApprProcDate` is null and `r`.`ApprProcName` is not null then ' Procurement Head' when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then ' BU FC' when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then ' BU Head' when `r`.`ApprDepmdDate` is null and `r`.`ApprDepmdName` is not null then ' Deputy MD' when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then ' MD' when `r`.`ApprFinanceDate` is null and `r`.`ApprFinanceName` is not null then ' Finance Committee' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' when `r`.`RequestStatus` = '5' then 'Waiting Payment' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprHrdverifikatorDate` is null and `r`.`ApprHrdverifikatorName` is not null then 2 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 3 when `r`.`ApprProcDate` is null and `r`.`ApprProcName` is not null then 4 when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then 5 when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then 6 when `r`.`ApprDepmdDate` is null and `r`.`ApprDepmdName` is not null then 7 when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then 8 when `r`.`ApprFinanceDate` is null and `r`.`ApprFinanceName` is not null then 9 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHrdverifikatorDate` is null and `r`.`ApprHrdverifikatorName` is not null then `r`.`ApprHrdverifikatorName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` when `r`.`ApprProcDate` is null and `r`.`ApprProcName` is not null then `r`.`ApprProcName` when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then `r`.`ApprBufcName` when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then `r`.`ApprBuheadName` when `r`.`ApprDepmdDate` is null and `r`.`ApprDepmdName` is not null then `r`.`ApprDepmdName` when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then `r`.`ApprMdName` when `r`.`ApprFinanceDate` is null and `r`.`ApprFinanceName` is not null then `r`.`ApprFinanceName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' when `r`.`RequestStatus` = '5' then 'Waiting Payment' else '' end AS `PersonHolding` from ((`vwadvancereportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwadvancereportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvancereportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvancereportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`hv`.`fullname` AS `ApprHrdverifikatorName`,case when `hv`.`ApprovalStatus` = '0' then 'Waiting Approval' when `hv`.`ApprovalStatus` = '1' then 'Rework' when `hv`.`ApprovalStatus` = '2' then 'Approved' when `hv`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdverifikatorStatus`,case when `hv`.`ApprovalStatus` not in ('0','4') then `hv`.`ApprovalDate` else NULL end AS `ApprHrdverifikatorDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate`,`pro`.`fullname` AS `ApprProcName`,case when `pro`.`ApprovalStatus` = '0' then 'Waiting Approval' when `pro`.`ApprovalStatus` = '1' then 'Rework' when `pro`.`ApprovalStatus` = '2' then 'Approved' when `pro`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprProcStatus`,case when `pro`.`ApprovalStatus` not in ('0','4') then `pro`.`ApprovalDate` else NULL end AS `ApprProcDate`,`buf`.`fullname` AS `ApprBufcName`,case when `buf`.`ApprovalStatus` = '0' then 'Waiting Approval' when `buf`.`ApprovalStatus` = '1' then 'Rework' when `buf`.`ApprovalStatus` = '2' then 'Approved' when `buf`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBufcStatus`,case when `buf`.`ApprovalStatus` not in ('0','4') then `buf`.`ApprovalDate` else NULL end AS `ApprBufcDate`,`b`.`fullname` AS `ApprBuheadName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuheadStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuheadDate`,`dmd`.`fullname` AS `ApprDepmdName`,case when `dmd`.`ApprovalStatus` = '0' then 'Waiting Approval' when `dmd`.`ApprovalStatus` = '1' then 'Rework' when `dmd`.`ApprovalStatus` = '2' then 'Approved' when `dmd`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDepmdStatus`,case when `dmd`.`ApprovalStatus` not in ('0','4') then `dmd`.`ApprovalDate` else NULL end AS `ApprDepmdDate`,`md`.`fullname` AS `ApprMdName`,case when `md`.`ApprovalStatus` = '0' then 'Waiting Approval' when `md`.`ApprovalStatus` = '1' then 'Rework' when `md`.`ApprovalStatus` = '2' then 'Approved' when `md`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprMdStatus`,case when `md`.`ApprovalStatus` not in ('0','4') then `md`.`ApprovalDate` else NULL end AS `ApprMdDate`,`i`.`fullname` AS `ApprFinanceName`,case when `i`.`ApprovalStatus` = '0' then 'Waiting Approval' when `i`.`ApprovalStatus` = '1' then 'Rework' when `i`.`ApprovalStatus` = '2' then 'Approved' when `i`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprFinanceStatus`,case when `i`.`ApprovalStatus` not in ('0','4') then `i`.`ApprovalDate` else NULL end AS `ApprFinanceDate` from (((((((((`tbl_advance` `r` left join `vwadvanceapprovedepthead` `d` on(`r`.`id` = `d`.`advance_id`)) left join `vwadvanceapprovehrdverifikator` `hv` on(`r`.`id` = `hv`.`advance_id`)) left join `vwadvanceapprovehrdhead` `h` on(`r`.`id` = `h`.`advance_id`)) left join `vwadvanceapproveproc` `pro` on(`r`.`id` = `pro`.`advance_id`)) left join `vwadvanceapprovebufc` `buf` on(`r`.`id` = `buf`.`advance_id`)) left join `vwadvanceapprovebuhead` `b` on(`r`.`id` = `b`.`advance_id`)) left join `vwadvanceapprovedepmd` `dmd` on(`r`.`id` = `dmd`.`advance_id`)) left join `vwadvanceapprovemd` `md` on(`r`.`id` = `md`.`advance_id`)) left join `vwadvanceapprovefinance` `i` on(`r`.`id` = `i`.`advance_id`)) ;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpenseapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpenseapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`advexpense_id` AS `advexpense_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advexpenseapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '36' ;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovehrdverifikator
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpenseapprovehrdverifikator`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpenseapprovehrdverifikator` AS select
    `a`.`id` as `id`,
    `a`.`advexpense_id` as `advexpense_id`,
    `a`.`approver_id` as `approver_id`,
    `a`.`ApprovalDate` as `ApprovalDate`,
    `a`.`ApprovalStatus` as `ApprovalStatus`,
    `a`.`Remarks` as `Remarks`,
    `p`.`employee_id` as `employee_id`,
    `e`.`FullName` as `fullname`,
    `d`.`DepartmentName` as `departmentname`,
    `p`.`approvaltype_id` as `approvaltype_id`,
    `p`.`sequence` as `sequence`,
    `p`.`isFinal` as `isFinal`
from
    (((`oasys_dev`.`tbl_advexpenseapproval` `a`
left join `oasys_dev`.`tbl_approver` `p` on
    (`a`.`approver_id` = `p`.`id`))
left join `oasys_dev`.`tbl_employee` `e` on
    (`p`.`employee_id` = `e`.`id`))
left join `oasys_dev`.`tbl_department` `d` on
    (`e`.`department_id` = `d`.`id`))
where
    `p`.`approvaltype_id` = '70' ;

-- Dumping structure for view oasys_dev.vwadvexpenseapprovesuperior
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpenseapprovesuperior`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpenseapprovesuperior` AS select `a`.`id` AS `id`,`a`.`advexpense_id` AS `advexpense_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advexpenseapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '35' ;

-- Dumping structure for view oasys_dev.vwadvexpensedetailbtcheck
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpensedetailbtcheck`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpensedetailbtcheck` AS select `tab`.`advexpense_id` AS `advexpense_id`,`tab`.`departdate` AS `departdate`,`tab`.`departtime` AS `departtime`,`tab`.`returndate` AS `returndate`,`tab`.`returntime` AS `returntime` from (select `a`.`advexpense_id` AS `advexpense_id`,`a`.`DepartDate` AS `departdate`,`a`.`DepartTime` AS `departtime`,`a`.`ReturnDate` AS `returndate`,`a`.`ReturnTime` AS `returntime` from (`oasys`.`tbl_advexpensedetail_bt` `a` left join `oasys`.`tbl_advexpense` `b` on(`a`.`advexpense_id` = `b`.`id`)) where `a`.`DepartDate` in (`b`.`StartDate`,`b`.`EndDate`)) `tab` where `tab`.`departtime` is null or `tab`.`returntime` is null ;

-- Dumping structure for view oasys_dev.vwadvexpenseforsap
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpenseforsap`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpenseforsap` AS select `ap`.`CreatedDate` AS `createddate`,`ec`.`SAPCode` AS `companycode`,`ec`.`CompanyName` AS `companyname`,`ap`.`RequestStatus` AS `requeststatus`,`ap`.`ExpenseNo` AS `expenseno`,`ap`.`PaymentType` AS `paymenttype`,`ap`.`AdvanceNo` AS `advanceno`,`ap`.`LessAdvance` AS `lessadvance`,`e`.`SAPID` AS `SAPID`,`ap`.`Name` AS `name`,`ap`.`Email` AS `email`,`ap`.`CostCenter` AS `costcenter`,`ap`.`Bg` AS `bg`,`ap`.`StartDate` AS `startdate`,`ap`.`EndDate` AS `enddate`,`ap`.`reason` AS `reason`,`ap`.`Remarks` AS `HeaderRemarks`,`ed`.`FullName` AS `DeptHead`,`ade`.`email` AS `DeptHeadEmail`,`ad`.`ExpenseType` AS `ExpenseType`,`ad`.`Purpose` AS `Purpose`,`ad`.`ReceiptDate` AS `ReceiptDate`,`ad`.`Amount` AS `Amount`,`ad`.`Currency` AS `Currency`,`ad`.`ExchangeRate` AS `ExchangeRate`,`ad`.`PaymentAmount` AS `PaymentAmount`,`ad`.`CostCentre` AS `CostCentre`,`ad`.`Country` AS `Country`,`ad`.`Location` AS `Location`,`ad`.`Remarks` AS `DetailRemarks` from ((((((`tbl_advexpensedetail` `ad` left join `tbl_advexpense` `ap` on(`ad`.`advexpense_id` = `ap`.`id`)) left join `tbl_employee` `e` on(`ap`.`employee_id` = `e`.`id`)) left join `tbl_addressbook` `a` on(`e`.`LoginName` = `a`.`username`)) left join `tbl_employee` `ed` on(`ap`.`Superior` = `ed`.`id`)) left join `tbl_company` `ec` on(`e`.`company_id` = `ec`.`id`)) left join `tbl_addressbook` `ade` on(`ed`.`LoginName` = `ade`.`username`)) ;

-- Dumping structure for view oasys_dev.vwadvexpensereport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpensereport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpensereport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`Superior` AS `Superior`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprSuperiorName` AS `ApprSuperiorName`,`r`.`ApprSuperiorStatus` AS `ApprSuperiorStatus`,`r`.`ApprSuperiorDate` AS `ApprSuperiorDate`,`r`.`ApprHrdVerifikatorName` AS `ApprHrdVerifikatorName`,`r`.`ApprHrdVerifikatorStatus` AS `ApprHrdVerifikatorStatus`,`r`.`ApprHrdVerifikatorDate` AS `ApprHrdVerifikatorDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then ' Dept Head' when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then ' HRD Verifikator' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then 1 when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then 2 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 3 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then `r`.`ApprSuperiorName` when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then `r`.`ApprHrdVerifikatorName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwadvexpensereportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwadvexpensereportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvexpensereportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvexpensereportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`Superior` AS `Superior`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprSuperiorName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprSuperiorStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprSuperiorDate`,`hv`.`fullname` AS `ApprHrdVerifikatorName`,case when `hv`.`ApprovalStatus` = '0' then 'Waiting Approval' when `hv`.`ApprovalStatus` = '1' then 'Rework' when `hv`.`ApprovalStatus` = '2' then 'Approved' when `hv`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdVerifikatorStatus`,case when `hv`.`ApprovalStatus` not in ('0','4') then `hv`.`ApprovalDate` else NULL end AS `ApprHrdVerifikatorDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate` from (((`tbl_advexpense` `r` left join `vwadvexpenseapprovesuperior` `d` on(`r`.`id` = `d`.`advexpense_id`)) left join `vwadvexpenseapprovehrdverifikator` `hv` on(`r`.`id` = `hv`.`advexpense_id`)) left join `vwadvexpenseapprovehrdhead` `h` on(`r`.`id` = `h`.`advexpense_id`)) ;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovebufc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentapprovebufc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentapprovebufc` AS select `a`.`id` AS `id`,`a`.`advpayment_id` AS `advpayment_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advpaymentapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '37' ;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentapprovebuhead` AS select `a`.`id` AS `id`,`a`.`advpayment_id` AS `advpayment_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advpaymentapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '38' ;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentapprovedepthead` AS select `a`.`id` AS `id`,`a`.`advpayment_id` AS `advpayment_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advpaymentapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '35' ;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`advpayment_id` AS `advpayment_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advpaymentapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '36' ;

-- Dumping structure for view oasys_dev.vwadvpaymentapprovehrdverifikator
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentapprovehrdverifikator`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentapprovehrdverifikator` AS select `a`.`id` AS `id`,`a`.`advpayment_id` AS `advpayment_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_advpaymentapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '44' ;

-- Dumping structure for view oasys_dev.vwadvpaymentforsap
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentforsap`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentforsap` AS select `ap`.`id` AS `id`,`ap`.`CreatedDate` AS `CreatedDate`,`ap`.`PaymentNo` AS `PaymentNo`,`ap`.`PaymentForm` AS `PaymentForm`,`ap`.`PaymentType` AS `PaymentType`,`e`.`SAPID` AS `SAPID`,`e`.`FullName` AS `Name`,`a`.`email` AS `Email`,`ap`.`RequestStatus` AS `RequestStatus`,`ap`.`AdvanceNo` AS `AdvanceNo`,`ap`.`LessAdvance` AS `LessAdvance`,`ap`.`Payment` AS `Payment`,`ap`.`Beneficiary` AS `Beneficiary`,`ap`.`AccountName` AS `AccountName`,`ap`.`Bank` AS `Bank`,`ap`.`AccountNumber` AS `AccountNumber`,`ap`.`NoVoucher` AS `NoVoucher`,`ap`.`DueDate` AS `DueDate`,`ap`.`PaymentDate` AS `PaymentDate`,`ap`.`reason` AS `Reason`,`ap`.`Remarks` AS `HeaderRemarks`,`ed`.`SAPID` AS `DeptHeadSAP_ID`,`ed`.`FullName` AS `DeptHead`,`ade`.`email` AS `DeptHeadEmail`,`ap`.`ApprovedDoc` AS `ApprovedDoc`,`ad`.`Description` AS `Description`,`ad`.`AccountCode` AS `AccountCode`,`ad`.`Amount` AS `Amount`,`ad`.`Remarks` AS `DetailRemarks` from (((((`tbl_advpaymentdetail` `ad` left join `tbl_advpayment` `ap` on(`ad`.`advpayment_id` = `ap`.`id`)) left join `tbl_employee` `e` on(`ap`.`employee_id` = `e`.`id`)) left join `tbl_addressbook` `a` on(`e`.`LoginName` = `a`.`username`)) left join `tbl_employee` `ed` on(`ap`.`DeptHead` = `ed`.`id`)) left join `tbl_addressbook` `ade` on(`ed`.`LoginName` = `ade`.`username`)) ;

-- Dumping structure for view oasys_dev.vwadvpaymentreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentreport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHrdVerifikatorName` AS `ApprHrdverifikatorName`,`r`.`ApprHrdVerifikatorStatus` AS `ApprHrdverifikatorStatus`,`r`.`ApprHrdVerifikatorDate` AS `ApprHrdverifikatorDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`r`.`ApprBufcName` AS `ApprBufcName`,`r`.`ApprBufcStatus` AS `ApprBufcStatus`,`r`.`ApprBufcDate` AS `ApprBufcDate`,`r`.`ApprBuheadName` AS `ApprBuheadName`,`r`.`ApprBuheadStatus` AS `ApprBuheadStatus`,`r`.`ApprBuheadDate` AS `ApprBuheadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then ' HRD Verifikator' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then ' BU FC' when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then ' BU Head' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then 2 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 3 when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then 4 when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then 5 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHrdVerifikatorDate` is null and `r`.`ApprHrdVerifikatorName` is not null then `r`.`ApprHrdVerifikatorName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` when `r`.`ApprBufcDate` is null and `r`.`ApprBufcName` is not null then `r`.`ApprBufcName` when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then `r`.`ApprBuheadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' when `r`.`RequestStatus` = '5' then 'Waiting Payment' else '' end AS `PersonHolding` from ((`vwadvpaymentreportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwadvpaymentreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwadvpaymentreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwadvpaymentreportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`hv`.`fullname` AS `ApprHrdVerifikatorName`,case when `hv`.`ApprovalStatus` = '0' then 'Waiting Approval' when `hv`.`ApprovalStatus` = '1' then 'Rework' when `hv`.`ApprovalStatus` = '2' then 'Approved' when `hv`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdVerifikatorStatus`,case when `hv`.`ApprovalStatus` not in ('0','4') then `hv`.`ApprovalDate` else NULL end AS `ApprHrdVerifikatorDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate`,`buf`.`fullname` AS `ApprBufcName`,case when `buf`.`ApprovalStatus` = '0' then 'Waiting Approval' when `buf`.`ApprovalStatus` = '1' then 'Rework' when `buf`.`ApprovalStatus` = '2' then 'Approved' when `buf`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBufcStatus`,case when `buf`.`ApprovalStatus` not in ('0','4') then `buf`.`ApprovalDate` else NULL end AS `ApprBufcDate`,`b`.`fullname` AS `ApprBuheadName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuheadStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuheadDate` from (((((`tbl_advpayment` `r` left join `vwadvpaymentapprovedepthead` `d` on(`r`.`id` = `d`.`advpayment_id`)) left join `vwadvpaymentapprovehrdverifikator` `hv` on(`r`.`id` = `hv`.`advpayment_id`)) left join `vwadvpaymentapprovehrdhead` `h` on(`r`.`id` = `h`.`advpayment_id`)) left join `vwadvpaymentapprovebufc` `buf` on(`r`.`id` = `buf`.`advpayment_id`)) left join `vwadvpaymentapprovebuhead` `b` on(`r`.`id` = `b`.`advpayment_id`)) ;

-- Dumping structure for view oasys_dev.vwallpending
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwallpending`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwallpending` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`r`.`FullName` AS `Reqby`,`e`.`company_id` AS `company_id`,`r`.`companyname` AS `companyname`,`r`.`DepartmentName` AS `departmentname`,`r`.`RequestStatus` AS `requeststatus`,`p`.`sequence` AS `sequence`,`e`.`FullName` AS `FullName` from (((`tbl_dayoffapproval` `a` left join `vwdayoffreq` `r` on(`a`.`dayoff_id` = `r`.`id`)) left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) where `a`.`ApprovalStatus` = 0 order by `a`.`dayoff_id`,`p`.`sequence` ;

-- Dumping structure for view oasys_dev.vwcontract
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwcontract`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwcontract` AS select `c`.`id` AS `id`,`c`.`rfc_id` AS `rfc_id`,`c`.`ContractNo` AS `ContractNo`,`c`.`contractor_id` AS `contractor_id`,`ct`.`ContractorName` AS `ContractorName`,`c`.`PeriodStart` AS `PeriodStart`,`c`.`PeriodEnd` AS `PeriodEnd`,`c`.`Description` AS `Description`,`c`.`ContractStatus` AS `ContractStatus`,`c`.`OldContractNo` AS `OldContractNo`,`c`.`NewContractNo` AS `NewContractNo`,`c`.`ContractDocument` AS `ContractDocument`,`c`.`CreatedBy` AS `CreatedBy`,`c`.`CreatedDate` AS `CreatedDate`,`c`.`ModifiedBy` AS `ModifiedBy`,`c`.`ModifiedDate` AS `ModifiedDate`,`a`.`ActivityDescr` AS `ActivityDescr`,`r`.`RFCNo` AS `RFCNo`,`r`.`RateType` AS `RateType`,`r`.`SKNo` AS `SKNo`,`r`.`CompanyCode` AS `CompanyCode`,`e`.`FullName` AS `RFCUser`,`ab`.`email` AS `RFCUserEmail`,`ab2`.`email` AS `RFCDeptHeadEmail`,`c`.`isActive` AS `isActive`,`c`.`isDeleted` AS `isDeleted` from (((((((`tbl_contract` `c` left join `tbl_rfc` `r` on(`c`.`rfc_id` = `r`.`id`)) left join `tbl_rfcactivity` `a` on(`r`.`activity_id` = `a`.`id`)) left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `vwaddressbook` `ab` on(`e`.`LoginName` = `ab`.`username`)) left join `tbl_employee` `e2` on(`r`.`depthead` = `e2`.`id`)) left join `vwaddressbook` `ab2` on(`e2`.`LoginName` = `ab2`.`username`)) left join `tbl_rfccontractor` `ct` on(`c`.`contractor_id` = `ct`.`id`)) ;

-- Dumping structure for view oasys_dev.vwdayoffapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffapprovebuhead` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_dayoffapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '2' ;

-- Dumping structure for view oasys_dev.vwdayoffapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffapprovedepthead` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_dayoffapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '1' ;

-- Dumping structure for view oasys_dev.vwdayoffapprovedeputymd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffapprovedeputymd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffapprovedeputymd` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_dayoffapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '3' ;

-- Dumping structure for view oasys_dev.vwdayoffapprovehrd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffapprovehrd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffapprovehrd` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_dayoffapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '4' ;

-- Dumping structure for view oasys_dev.vwdayoffapprovesuperior
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffapprovesuperior`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffapprovesuperior` AS select `a`.`id` AS `id`,`a`.`dayoff_id` AS `dayoff_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_dayoffapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '5' ;

-- Dumping structure for view oasys_dev.vwdayoffreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffreport` AS select `r`.`id` AS `id`,`r`.`RequestDate` AS `RequestDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Remarks` AS `Remarks`,`r`.`Superior` AS `Superior`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprSuperiorName` AS `ApprSuperiorName`,`r`.`ApprSuperiorStatus` AS `ApprSuperiorStatus`,`r`.`ApprSuperiorDate` AS `ApprSuperiorDate`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprBuHeadName` AS `ApprBuHeadName`,`r`.`ApprBuHeadStatus` AS `ApprBuHeadStatus`,`r`.`ApprBuHeadDate` AS `ApprBuHeadDate`,`r`.`ApprDeputyMdName` AS `ApprDeputyMdName`,`r`.`ApprDeputyMdStatus` AS `ApprDeputyMdStatus`,`r`.`ApprDeputyMdDate` AS `ApprDeputyMdDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then ' Superior' when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprBuHeadDate` is null and `r`.`ApprBuHeadName` is not null then ' Bu Head' when `r`.`ApprDeputyMdDate` is null and `r`.`ApprDeputyMdName` is not null then ' Deputy MD' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then 1 when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 3 when `r`.`ApprBuHeadDate` is null and `r`.`ApprBuHeadName` is not null then 4 when `r`.`ApprDeputyMdDate` is null and `r`.`ApprDeputyMdName` is not null then 5 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 6 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then `r`.`ApprSuperiorName` when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprBuHeadDate` is null and `r`.`ApprBuHeadName` is not null then `r`.`ApprBuHeadName` when `r`.`ApprDeputyMdDate` is null and `r`.`ApprDeputyMdName` is not null then `r`.`ApprDeputyMdName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwdayoffreportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwdayoffreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffreportsubquery` AS select `r`.`id` AS `id`,`r`.`RequestDate` AS `RequestDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Remarks` AS `Remarks`,`r`.`Superior` AS `Superior`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprSuperiorName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprSuperiorStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprSuperiorDate`,`dh`.`fullname` AS `ApprDeptHeadName`,case when `dh`.`ApprovalStatus` = '0' then 'Waiting Approval' when `dh`.`ApprovalStatus` = '1' then 'Rework' when `dh`.`ApprovalStatus` = '2' then 'Approved' when `dh`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `dh`.`ApprovalStatus` not in ('0','4') then `dh`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`bu`.`fullname` AS `ApprBuHeadName`,case when `bu`.`ApprovalStatus` = '0' then 'Waiting Approval' when `bu`.`ApprovalStatus` = '1' then 'Rework' when `bu`.`ApprovalStatus` = '2' then 'Approved' when `bu`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuHeadStatus`,case when `bu`.`ApprovalStatus` not in ('0','4') then `bu`.`ApprovalDate` else NULL end AS `ApprBuHeadDate`,`dmd`.`fullname` AS `ApprDeputyMdName`,case when `dmd`.`ApprovalStatus` = '0' then 'Waiting Approval' when `dmd`.`ApprovalStatus` = '1' then 'Rework' when `dmd`.`ApprovalStatus` = '2' then 'Approved' when `dmd`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeputyMdStatus`,case when `dmd`.`ApprovalStatus` not in ('0','4') then `dmd`.`ApprovalDate` else NULL end AS `ApprDeputyMdDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate` from (((((`tbl_dayoffreq` `r` left join `vwdayoffapprovesuperior` `d` on(`r`.`id` = `d`.`dayoff_id`)) left join `vwdayoffapprovedepthead` `dh` on(`r`.`id` = `dh`.`dayoff_id`)) left join `vwdayoffapprovebuhead` `bu` on(`r`.`id` = `bu`.`dayoff_id`)) left join `vwdayoffapprovedeputymd` `dmd` on(`r`.`id` = `dmd`.`dayoff_id`)) left join `vwdayoffapprovehrd` `h` on(`r`.`id` = `h`.`dayoff_id`)) ;

-- Dumping structure for view oasys_dev.vwdayoffreq
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwdayoffreq`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwdayoffreq` AS select `dr`.`id` AS `id`,`dr`.`RequestDate` AS `RequestDate`,`dr`.`RequestStatus` AS `RequestStatus`,case when `dr`.`RequestStatus` = '0' then 'Saved as Draft' when `dr`.`RequestStatus` = '1' then 'Waiting Approval' when `dr`.`RequestStatus` = '2' then 'Require Rework' when `dr`.`RequestStatus` = '3' then 'Approved' when `dr`.`RequestStatus` = '4' then 'Rejected' else '' end AS `ReqStatus`,`dr`.`employee_id` AS `employee_id`,`em`.`FullName` AS `FullName`,`dr`.`Superior` AS `superior`,`sp`.`FullName` AS `SuperiorName`,`dr`.`DeptHead` AS `depthead`,`dh`.`FullName` AS `DeptHeadName`,`em`.`SAPID` AS `SAPID`,`c`.`CompanyName` AS `companyname`,`d`.`DepartmentName` AS `DepartmentName`,`dr`.`ApprovedDoc` AS `ApprovedDoc`,`dr`.`Remarks` AS `Remarks` from (((((`tbl_dayoffreq` `dr` left join `tbl_employee` `em` on(`dr`.`employee_id` = `em`.`id`)) left join `tbl_company` `c` on(`em`.`company_id` = `c`.`id`)) left join `tbl_department` `d` on(`em`.`department_id` = `d`.`id`)) left join `tbl_employee` `sp` on(`dr`.`Superior` = `sp`.`id`)) left join `tbl_employee` `dh` on(`dr`.`DeptHead` = `dh`.`id`)) ;

-- Dumping structure for view oasys_dev.vwemptemplate
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwemptemplate`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwemptemplate` AS select `e`.`SAPID` AS `SAPID`,`e`.`FullName` AS `Name`,`c`.`SAPCode` AS `PA`,`c`.`CompanyName` AS `PAText`,`l`.`SAPCode` AS `SubArea`,`l`.`Location` AS `SubAreaText`,`e`.`CostCenter` AS `CostCenter`,`d`.`SAPCode` AS `OU`,`d`.`DepartmentName` AS `OUText`,`ds`.`SAPCode` AS `Position`,`ds`.`DesignationName` AS `PositionText`,`lv`.`Level` AS `Level`,'' AS `MappingLevel`,'' AS `Mapping`,`e`.`LoginName` AS `LoginName`,`e`.`SAPID` AS `OldSAPID`,`e`.`companycode` AS `companycode` from (((((`tbl_employee` `e` left join `tbl_company` `c` on(`e`.`company_id` = `c`.`id`)) left join `tbl_location` `l` on(`e`.`location_id` = `l`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) left join `tbl_designation` `ds` on(`e`.`designation_id` = `ds`.`id`)) left join `tbl_level` `lv` on(`e`.`level_id` = `lv`.`id`)) ;

-- Dumping structure for view oasys_dev.vwexporthc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwexporthc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwexporthc` AS select '' AS `no`,`e`.`SAPID` AS `SAPID`,`e`.`FullName` AS `NAME`,`c`.`SAPCode` AS `PA`,`c`.`CompanyName` AS `PAText`,`l`.`SAPCode` AS `SubArea`,`l`.`Location` AS `SubAreaText`,`e`.`CostCenter` AS `CostCenter`,`d`.`SAPCode` AS `OU`,`d`.`DepartmentName` AS `OUText`,`ds`.`SAPCode` AS `position`,`ds`.`DesignationName` AS `PositionText`,`lv`.`Level` AS `Grade`,case when `e`.`level_id` in ('2','3') then 'Staff' else `lv`.`Level` end AS `MappingLevel`,'' AS `Mapping`,`e`.`LoginName` AS `UserName`,`e`.`SAPID` AS `OldSAP`,`e`.`companycode` AS `CompanyCodeUsed`,`e`.`JoinDate` AS `JoinDate` from (((((`tbl_employee` `e` left join `tbl_company` `c` on(`e`.`company_id` = `c`.`id`)) left join `tbl_location` `l` on(`e`.`location_id` = `l`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) left join `tbl_designation` `ds` on(`e`.`designation_id` = `ds`.`id`)) left join `tbl_level` `lv` on(`e`.`level_id` = `lv`.`id`)) ;

-- Dumping structure for view oasys_dev.vwiteieapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteieapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteieapprovebuhead` AS select `a`.`id` AS `id`,`a`.`iteie_id` AS `iteie_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_iteieapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '31' ;

-- Dumping structure for view oasys_dev.vwiteieapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteieapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteieapprovedepthead` AS select `a`.`id` AS `id`,`a`.`iteie_id` AS `iteie_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_iteieapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '29' ;

-- Dumping structure for view oasys_dev.vwiteieapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteieapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteieapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`iteie_id` AS `iteie_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_iteieapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '30' ;

-- Dumping structure for view oasys_dev.vwiteieapproveitlead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteieapproveitlead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteieapproveitlead` AS select `a`.`id` AS `id`,`a`.`iteie_id` AS `iteie_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_iteieapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '32' ;

-- Dumping structure for view oasys_dev.vwiteiereport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteiereport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteiereport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`Department` AS `Department`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Name` AS `Name`,`r`.`EmployeeID` AS `EmployeeID`,`r`.`DepartmentUser` AS `DepartmentUser`,`r`.`Designation` AS `Designation`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`isVip` AS `isVip`,`r`.`AccessType` AS `AccessType`,`r`.`AccountType` AS `AccountType`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`ListGroup` AS `ListGroup`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`r`.`ApprBuheadName` AS `ApprBuheadName`,`r`.`ApprBuheadStatus` AS `ApprBuheadStatus`,`r`.`ApprBuheadDate` AS `ApprBuheadDate`,`r`.`ApprItleadName` AS `ApprItleadName`,`r`.`ApprItleadStatus` AS `ApprItleadStatus`,`r`.`ApprItleadDate` AS `ApprItleadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then ' BU Head' when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then ' IT Lead' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 2 when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then 3 when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then 5 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then `r`.`ApprBuheadName` when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then `r`.`ApprItleadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwiteiereportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwiteiereportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwiteiereportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwiteiereportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`Department` AS `Department`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Name` AS `Name`,`r`.`EmployeeID` AS `EmployeeID`,`r`.`DepartmentUser` AS `DepartmentUser`,`r`.`Designation` AS `Designation`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`isVip` AS `isVip`,`r`.`AccessType` AS `AccessType`,`r`.`AccountType` AS `AccountType`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`ListGroup` AS `ListGroup`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate`,`b`.`fullname` AS `ApprBuheadName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuheadStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuheadDate`,`i`.`fullname` AS `ApprItleadName`,case when `i`.`ApprovalStatus` = '0' then 'Waiting Approval' when `i`.`ApprovalStatus` = '1' then 'Rework' when `i`.`ApprovalStatus` = '2' then 'Approved' when `i`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprItleadStatus`,case when `i`.`ApprovalStatus` not in ('0','4') then `i`.`ApprovalDate` else NULL end AS `ApprItleadDate` from ((((`tbl_iteie` `r` left join `vwiteieapprovedepthead` `d` on(`r`.`id` = `d`.`iteie_id`)) left join `vwiteieapprovehrdhead` `h` on(`r`.`id` = `h`.`iteie_id`)) left join `vwiteieapprovebuhead` `b` on(`r`.`id` = `b`.`iteie_id`)) left join `vwiteieapproveitlead` `i` on(`r`.`id` = `i`.`iteie_id`)) ;

-- Dumping structure for view oasys_dev.vwitimailapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailapprovebuhead` AS select `a`.`id` AS `id`,`a`.`itimail_id` AS `itimail_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itimailapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '31' ;

-- Dumping structure for view oasys_dev.vwitimailapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailapprovedepthead` AS select `a`.`id` AS `id`,`a`.`itimail_id` AS `itimail_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itimailapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '29' ;

-- Dumping structure for view oasys_dev.vwitimailapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`itimail_id` AS `itimail_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itimailapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '30' ;

-- Dumping structure for view oasys_dev.vwitimailapproveitlead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailapproveitlead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailapproveitlead` AS select `a`.`id` AS `id`,`a`.`itimail_id` AS `itimail_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itimailapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '32' ;

-- Dumping structure for view oasys_dev.vwitimailapprovemd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailapprovemd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailapprovemd` AS select `a`.`id` AS `id`,`a`.`itimail_id` AS `itimail_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itimailapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '33' ;

-- Dumping structure for view oasys_dev.vwitimailreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailreport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`FormType` AS `FormType`,`r`.`Department` AS `Department`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Designation` AS `Designation`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`AccessRequested` AS `AccessRequested`,`r`.`AccessType` AS `AccessType`,`r`.`AccountType` AS `AccountType`,`r`.`EmailQuota` AS `EmailQuota`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`EmailDomain` AS `EmailDomain`,`r`.`ListGroup` AS `ListGroup`,`r`.`ListGroupModeration` AS `ListGroupModeration`,`r`.`web1` AS `web1`,`r`.`web2` AS `web2`,`r`.`NewMailBoxSize` AS `NewMailBoxSize`,`r`.`IncomingSize` AS `IncomingSize`,`r`.`OutgoingSize` AS `OutgoingSize`,`r`.`TypeOfAccess` AS `TypeOfAccess`,`r`.`EmailGroupName` AS `EmailGroupName`,`r`.`MemberName` AS `MemberName`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`isDeclaration` AS `isDeclaration`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`r`.`ApprBuheadName` AS `ApprBuheadName`,`r`.`ApprBuheadStatus` AS `ApprBuheadStatus`,`r`.`ApprBuheadDate` AS `ApprBuheadDate`,`r`.`ApprMdName` AS `ApprMdName`,`r`.`ApprMdStatus` AS `ApprMdStatus`,`r`.`ApprMdDate` AS `ApprMdDate`,`r`.`ApprItleadName` AS `ApprItleadName`,`r`.`ApprItleadStatus` AS `ApprItleadStatus`,`r`.`ApprItleadDate` AS `ApprItleadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then ' BU Head' when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then ' MD' when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then ' IT Lead' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 2 when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then 3 when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then 4 when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then 5 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then `r`.`ApprBuheadName` when `r`.`ApprMdDate` is null and `r`.`ApprMdName` is not null then `r`.`ApprMdName` when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then `r`.`ApprItleadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwitimailreportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwitimailreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitimailreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitimailreportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`FormType` AS `FormType`,`r`.`Department` AS `Department`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Designation` AS `Designation`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`AccessRequested` AS `AccessRequested`,`r`.`AccessType` AS `AccessType`,`r`.`AccountType` AS `AccountType`,`r`.`EmailQuota` AS `EmailQuota`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`EmailDomain` AS `EmailDomain`,`r`.`ListGroup` AS `ListGroup`,`r`.`ListGroupModeration` AS `ListGroupModeration`,`r`.`web1` AS `web1`,`r`.`web2` AS `web2`,`r`.`NewMailBoxSize` AS `NewMailBoxSize`,`r`.`IncomingSize` AS `IncomingSize`,`r`.`OutgoingSize` AS `OutgoingSize`,`r`.`TypeOfAccess` AS `TypeOfAccess`,`r`.`EmailGroupName` AS `EmailGroupName`,`r`.`MemberName` AS `MemberName`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`isDeclaration` AS `isDeclaration`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate`,`b`.`fullname` AS `ApprBuheadName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuheadStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuheadDate`,`m`.`fullname` AS `ApprMdName`,case when `m`.`ApprovalStatus` = '0' then 'Waiting Approval' when `m`.`ApprovalStatus` = '1' then 'Rework' when `m`.`ApprovalStatus` = '2' then 'Approved' when `m`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprMdStatus`,case when `m`.`ApprovalStatus` not in ('0','4') then `m`.`ApprovalDate` else NULL end AS `ApprMdDate`,`i`.`fullname` AS `ApprItleadName`,case when `i`.`ApprovalStatus` = '0' then 'Waiting Approval' when `i`.`ApprovalStatus` = '1' then 'Rework' when `i`.`ApprovalStatus` = '2' then 'Approved' when `i`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprItleadStatus`,case when `i`.`ApprovalStatus` not in ('0','4') then `i`.`ApprovalDate` else NULL end AS `ApprItleadDate` from (((((`tbl_itimail` `r` left join `vwitimailapprovedepthead` `d` on(`r`.`id` = `d`.`itimail_id`)) left join `vwitimailapprovehrdhead` `h` on(`r`.`id` = `h`.`itimail_id`)) left join `vwitimailapprovebuhead` `b` on(`r`.`id` = `b`.`itimail_id`)) left join `vwitimailapproveitlead` `i` on(`r`.`id` = `i`.`itimail_id`)) left join `vwitimailapprovemd` `m` on(`r`.`id` = `m`.`itimail_id`)) ;

-- Dumping structure for view oasys_dev.vwitsharefapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefapprovebuhead` AS select `a`.`id` AS `id`,`a`.`itsharef_id` AS `itsharef_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itsharefapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '31' and `a`.`ApprovalStatus` = 0 ;

-- Dumping structure for view oasys_dev.vwitsharefapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefapprovedepthead` AS select `a`.`id` AS `id`,`a`.`itsharef_id` AS `itsharef_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itsharefapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '29' ;

-- Dumping structure for view oasys_dev.vwitsharefapprovehrdhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefapprovehrdhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefapprovehrdhead` AS select `a`.`id` AS `id`,`a`.`itsharef_id` AS `itsharef_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itsharefapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '30' ;

-- Dumping structure for view oasys_dev.vwitsharefapproveitlead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefapproveitlead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefapproveitlead` AS select `a`.`id` AS `id`,`a`.`itsharef_id` AS `itsharef_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itsharefapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '32' ;

-- Dumping structure for view oasys_dev.vwitsharefapproveitsite
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefapproveitsite`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefapproveitsite` AS select `a`.`id` AS `id`,`a`.`itsharef_id` AS `itsharef_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_itsharefapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '34' ;

-- Dumping structure for view oasys_dev.vwitsharefreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefreport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`Designation` AS `Designation`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Grade` AS `Grade`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`Department` AS `Department`,`r`.`RequestType` AS `RequestType`,`r`.`FolderName` AS `FolderName`,`r`.`AccountType` AS `AccountType`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`isDeclaration` AS `isDeclaration`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHrdHeadName` AS `ApprHrdHeadName`,`r`.`ApprHrdHeadStatus` AS `ApprHrdHeadStatus`,`r`.`ApprHrdHeadDate` AS `ApprHrdHeadDate`,`r`.`ApprBuheadName` AS `ApprBuheadName`,`r`.`ApprBuheadStatus` AS `ApprBuheadStatus`,`r`.`ApprBuheadDate` AS `ApprBuheadDate`,`r`.`ApprItsiteName` AS `ApprItsiteName`,`r`.`ApprItsiteStatus` AS `ApprItsiteStatus`,`r`.`ApprItsiteDate` AS `ApprItsiteDate`,`r`.`ApprItleadName` AS `ApprItleadName`,`r`.`ApprItleadStatus` AS `ApprItleadStatus`,`r`.`ApprItleadDate` AS `ApprItleadDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `DepartmentR`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then ' HRD Head' when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then ' BU Head' when `r`.`ApprItsiteDate` is null and `r`.`ApprItsiteName` is not null then ' IT Site' when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then ' IT Lead' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then 2 when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then 3 when `r`.`ApprItsiteDate` is null and `r`.`ApprItsiteName` is not null then 4 when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then 5 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHrdHeadDate` is null and `r`.`ApprHrdHeadName` is not null then `r`.`ApprHrdHeadName` when `r`.`ApprBuheadDate` is null and `r`.`ApprBuheadName` is not null then `r`.`ApprBuheadName` when `r`.`ApprItsiteDate` is null and `r`.`ApprItsiteName` is not null then `r`.`ApprItsiteName` when `r`.`ApprItleadDate` is null and `r`.`ApprItleadName` is not null then `r`.`ApprItleadName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`oasys`.`vwitsharefreportsubquery` `r` left join `oasys`.`tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `oasys`.`tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwitsharefreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwitsharefreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwitsharefreportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`Designation` AS `Designation`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`Grade` AS `Grade`,`r`.`BgBu` AS `BgBu`,`r`.`OfficeLocation` AS `OfficeLocation`,`r`.`Floor` AS `Floor`,`r`.`PhoneExt` AS `PhoneExt`,`r`.`Department` AS `Department`,`r`.`RequestType` AS `RequestType`,`r`.`FolderName` AS `FolderName`,`r`.`AccountType` AS `AccountType`,`r`.`ValidFrom` AS `ValidFrom`,`r`.`ValidTo` AS `ValidTo`,`r`.`reason` AS `reason`,`r`.`Remarks` AS `Remarks`,`r`.`isDeclaration` AS `isDeclaration`,`r`.`DeptHead` AS `DeptHead`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' or `d`.`ApprovalStatus` = '4' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprHrdHeadName`,case when `h`.`ApprovalStatus` = '0' or `d`.`ApprovalStatus` = '4' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHrdHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHrdHeadDate`,`b`.`fullname` AS `ApprBuheadName`,case when `b`.`ApprovalStatus` = '0' or `b`.`ApprovalStatus` = '4' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuheadStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuheadDate`,`m`.`fullname` AS `ApprItsiteName`,case when `m`.`ApprovalStatus` = '0' or `m`.`ApprovalStatus` = '4' then 'Waiting Approval' when `m`.`ApprovalStatus` = '1' then 'Rework' when `m`.`ApprovalStatus` = '2' then 'Approved' when `m`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprItsiteStatus`,case when `m`.`ApprovalStatus` not in ('0','4') then `m`.`ApprovalDate` else NULL end AS `ApprItsiteDate`,`i`.`fullname` AS `ApprItleadName`,case when `i`.`ApprovalStatus` = '0' or `i`.`ApprovalStatus` = '4' then 'Waiting Approval' when `i`.`ApprovalStatus` = '1' then 'Rework' when `i`.`ApprovalStatus` = '2' then 'Approved' when `i`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprItleadStatus`,case when `i`.`ApprovalStatus` not in ('0','4') then `i`.`ApprovalDate` else NULL end AS `ApprItleadDate` from (((((`oasys`.`tbl_itsharef` `r` left join (select `vwitsharefapprovedepthead`.`id` AS `id`,`vwitsharefapprovedepthead`.`itsharef_id` AS `itsharef_id`,`vwitsharefapprovedepthead`.`approver_id` AS `approver_id`,`vwitsharefapprovedepthead`.`ApprovalDate` AS `ApprovalDate`,`vwitsharefapprovedepthead`.`ApprovalStatus` AS `ApprovalStatus`,`vwitsharefapprovedepthead`.`Remarks` AS `Remarks`,`vwitsharefapprovedepthead`.`employee_id` AS `employee_id`,`vwitsharefapprovedepthead`.`fullname` AS `fullname`,`vwitsharefapprovedepthead`.`departmentname` AS `departmentname`,`vwitsharefapprovedepthead`.`approvaltype_id` AS `approvaltype_id`,`vwitsharefapprovedepthead`.`sequence` AS `sequence`,`vwitsharefapprovedepthead`.`isFinal` AS `isFinal` from `oasys`.`vwitsharefapprovedepthead` where `vwitsharefapprovedepthead`.`ApprovalStatus` = 0) `d` on(`r`.`id` = `d`.`itsharef_id`)) left join `oasys`.`vwitsharefapprovehrdhead` `h` on(`r`.`id` = `h`.`itsharef_id`)) left join (select `vwitsharefapprovebuhead`.`id` AS `id`,`vwitsharefapprovebuhead`.`itsharef_id` AS `itsharef_id`,`vwitsharefapprovebuhead`.`approver_id` AS `approver_id`,`vwitsharefapprovebuhead`.`ApprovalDate` AS `ApprovalDate`,`vwitsharefapprovebuhead`.`ApprovalStatus` AS `ApprovalStatus`,`vwitsharefapprovebuhead`.`Remarks` AS `Remarks`,`vwitsharefapprovebuhead`.`employee_id` AS `employee_id`,`vwitsharefapprovebuhead`.`fullname` AS `fullname`,`vwitsharefapprovebuhead`.`departmentname` AS `departmentname`,`vwitsharefapprovebuhead`.`approvaltype_id` AS `approvaltype_id`,`vwitsharefapprovebuhead`.`sequence` AS `sequence`,`vwitsharefapprovebuhead`.`isFinal` AS `isFinal` from `oasys`.`vwitsharefapprovebuhead` where `vwitsharefapprovebuhead`.`ApprovalStatus` = 0) `b` on(`r`.`id` = `b`.`itsharef_id`)) left join `oasys`.`vwitsharefapproveitsite` `m` on(`r`.`id` = `m`.`itsharef_id`)) left join `oasys`.`vwitsharefapproveitlead` `i` on(`r`.`id` = `i`.`itsharef_id`)) group by `r`.`id` ;

-- Dumping structure for view oasys_dev.vwmmf28approvebuyer
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf28approvebuyer`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf28approvebuyer` AS select `a`.`id` AS `id`,`a`.`mmf28_id` AS `mmf28_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf28approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '25' ;

-- Dumping structure for view oasys_dev.vwmmf28approvedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf28approvedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf28approvedepthead` AS select `a`.`id` AS `id`,`a`.`mmf28_id` AS `mmf28_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf28approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '23' ;

-- Dumping structure for view oasys_dev.vwmmf28approveprochead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf28approveprochead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf28approveprochead` AS select `a`.`id` AS `id`,`a`.`mmf28_id` AS `mmf28_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf28approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '24' ;

-- Dumping structure for view oasys_dev.vwmmf28report
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf28report`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf28report` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`WONumber` AS `WONumber`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`TelpNo` AS `TelpNo`,`r`.`ChargeCode` AS `ChargeCode`,`r`.`MaterialDispatch` AS `MaterialDispatch`,`r`.`RequiredDate` AS `RequiredDate`,`r`.`MaterialCode` AS `MaterialCode`,`r`.`MaterialDescr` AS `MaterialDescr`,`r`.`Symptomps` AS `Symptomps`,`r`.`RequiredType` AS `RequiredType`,`r`.`RequiredOther` AS `RequiredOther`,`r`.`Instruction` AS `Instruction`,`r`.`isHazardousChemical` AS `isHazardousChemical`,`r`.`HazChemicalName` AS `HazChemicalName`,`r`.`isDecontaminated` AS `isDecontaminated`,`r`.`isNotContaminated` AS `isNotContaminated`,`r`.`NotContaminatedReason` AS `NotContaminatedReason`,`r`.`isNonHazardous` AS `isNonHazardous`,`r`.`NonHazChemicalName` AS `NonHazChemicalName`,`r`.`isNonChemical` AS `isNonChemical`,`r`.`Remarks` AS `Remarks`,`r`.`MaterialDispatchNo` AS `MaterialDispatchNo`,`r`.`isRepair` AS `isRepair`,`r`.`isScrap` AS `isScrap`,`r`.`EstimateCost` AS `EstimateCost`,`r`.`PONo` AS `PONo`,`r`.`MaterialReturnedDate` AS `MaterialReturnedDate`,`r`.`SupplierDODNNo` AS `SupplierDODNNo`,`r`.`DeptHead` AS `DeptHead`,`r`.`Buyer` AS `Buyer`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprProcHeadName` AS `ApprProcHeadName`,`r`.`ApprProcHeadStatus` AS `ApprProcHeadStatus`,`r`.`ApprProcHeadDate` AS `ApprProcHeadDate`,`r`.`ApprBuyerName` AS `ApprBuyerName`,`r`.`ApprBuyerStatus` AS `ApprBuyerStatus`,`r`.`ApprBuyerDate` AS `ApprBuyerDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `Department`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then ' Procurement Head' when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then ' Buyer' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then 2 when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then 3 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then `r`.`ApprProcHeadName` when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then `r`.`ApprBuyerName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwmmf28reportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwmmf28reportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf28reportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf28reportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`WONumber` AS `WONumber`,`r`.`MMFNumber` AS `MMFNumber`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`TelpNo` AS `TelpNo`,`r`.`ChargeCode` AS `ChargeCode`,`r`.`MaterialDispatch` AS `MaterialDispatch`,`r`.`RequiredDate` AS `RequiredDate`,`r`.`MaterialCode` AS `MaterialCode`,`r`.`MaterialDescr` AS `MaterialDescr`,`r`.`Symptomps` AS `Symptomps`,`r`.`RequiredType` AS `RequiredType`,`r`.`RequiredOther` AS `RequiredOther`,`r`.`Instruction` AS `Instruction`,`r`.`isHazardousChemical` AS `isHazardousChemical`,`r`.`HazChemicalName` AS `HazChemicalName`,`r`.`isDecontaminated` AS `isDecontaminated`,`r`.`isNotContaminated` AS `isNotContaminated`,`r`.`NotContaminatedReason` AS `NotContaminatedReason`,`r`.`isNonHazardous` AS `isNonHazardous`,`r`.`NonHazChemicalName` AS `NonHazChemicalName`,`r`.`isNonChemical` AS `isNonChemical`,`r`.`Remarks` AS `Remarks`,`r`.`MaterialDispatchNo` AS `MaterialDispatchNo`,`r`.`isRepair` AS `isRepair`,`r`.`isScrap` AS `isScrap`,`r`.`EstimateCost` AS `EstimateCost`,`r`.`PONo` AS `PONo`,`r`.`MaterialReturnedDate` AS `MaterialReturnedDate`,`r`.`SupplierDODNNo` AS `SupplierDODNNo`,`r`.`action` AS `action`,`r`.`DeptHead` AS `DeptHead`,`r`.`Buyer` AS `Buyer`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprProcHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprProcHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprProcHeadDate`,`b`.`fullname` AS `ApprBuyerName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuyerStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuyerDate` from (((`tbl_mmf28` `r` left join `vwmmf28approvedepthead` `d` on(`r`.`id` = `d`.`mmf28_id`)) left join `vwmmf28approveprochead` `h` on(`r`.`id` = `h`.`mmf28_id`)) left join `vwmmf28approvebuyer` `b` on(`r`.`id` = `b`.`mmf28_id`)) ;

-- Dumping structure for view oasys_dev.vwmmf30approvebuyer
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf30approvebuyer`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf30approvebuyer` AS select `a`.`id` AS `id`,`a`.`mmf30_id` AS `mmf30_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`a`.`ProcComments` AS `ProcComments`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf30approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '28' ;

-- Dumping structure for view oasys_dev.vwmmf30approvedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf30approvedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf30approvedepthead` AS select `a`.`id` AS `id`,`a`.`mmf30_id` AS `mmf30_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`a`.`ProcComments` AS `ProcComments`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf30approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '26' ;

-- Dumping structure for view oasys_dev.vwmmf30approveprochead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf30approveprochead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf30approveprochead` AS select `a`.`id` AS `id`,`a`.`mmf30_id` AS `mmf30_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`a`.`ProcComments` AS `ProcComments`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_mmf30approval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '27' ;

-- Dumping structure for view oasys_dev.vwmmf30report
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf30report`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf30report` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`PRType` AS `PRType`,`r`.`RequisitionType` AS `RequisitionType`,`r`.`RequisitionOther` AS `RequisitionOther`,`r`.`PRNo` AS `PRNo`,`r`.`DeliverTo` AS `DeliverTo`,`r`.`CostCode` AS `CostCode`,`r`.`CostElement` AS `CostElement`,`r`.`SupplierName` AS `SupplierName`,`r`.`SupplierAddress` AS `SupplierAddress`,`r`.`SupplierEmailFax` AS `SupplierEmailFax`,`r`.`ContractNo` AS `ContractNo`,`r`.`ReceivedOn` AS `ReceivedOn`,`r`.`ReceivedByName` AS `ReceivedByName`,`r`.`SupplierContractNo` AS `SupplierContractNo`,`r`.`Reason` AS `Reason`,`r`.`RemarksU` AS `RemarksU`,`r`.`ProcComments` AS `ProcComments`,`r`.`DeptHead` AS `DeptHead`,`r`.`Buyer` AS `Buyer`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprProcHeadName` AS `ApprProcHeadName`,`r`.`ApprProcHeadStatus` AS `ApprProcHeadStatus`,`r`.`ApprProcHeadDate` AS `ApprProcHeadDate`,`r`.`ApprBuyerName` AS `ApprBuyerName`,`r`.`ApprBuyerStatus` AS `ApprBuyerStatus`,`r`.`ApprBuyerDate` AS `ApprBuyerDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `Department`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then ' Procurement Head' when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then ' Buyer' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then 1 when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then 2 when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then 3 else '' end else 0 end AS `ApprStatusCode`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprProcHeadDate` is null and `r`.`ApprProcHeadName` is not null then `r`.`ApprProcHeadName` when `r`.`ApprBuyerDate` is null and `r`.`ApprBuyerName` is not null then `r`.`ApprBuyerName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwmmf30reportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwmmf30reportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwmmf30reportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwmmf30reportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`PRType` AS `PRType`,`r`.`RequisitionType` AS `RequisitionType`,`r`.`RequisitionOther` AS `RequisitionOther`,`r`.`PRNo` AS `PRNo`,`r`.`DeliverTo` AS `DeliverTo`,`r`.`CostCode` AS `CostCode`,`r`.`CostElement` AS `CostElement`,`r`.`SupplierName` AS `SupplierName`,`r`.`SupplierAddress` AS `SupplierAddress`,`r`.`SupplierEmailFax` AS `SupplierEmailFax`,`r`.`ContractNo` AS `ContractNo`,`r`.`ReceivedOn` AS `ReceivedOn`,`r`.`ReceivedByName` AS `ReceivedByName`,`r`.`SupplierContractNo` AS `SupplierContractNo`,`r`.`Reason` AS `Reason`,`r`.`RemarksU` AS `RemarksU`,`r`.`ProcComments` AS `ProcComments`,`r`.`DeptHead` AS `DeptHead`,`r`.`Buyer` AS `Buyer`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprProcHeadName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprProcHeadStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprProcHeadDate`,`b`.`fullname` AS `ApprBuyerName`,case when `b`.`ApprovalStatus` = '0' then 'Waiting Approval' when `b`.`ApprovalStatus` = '1' then 'Rework' when `b`.`ApprovalStatus` = '2' then 'Approved' when `b`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBuyerStatus`,case when `b`.`ApprovalStatus` not in ('0','4') then `b`.`ApprovalDate` else NULL end AS `ApprBuyerDate` from (((`tbl_mmf30` `r` left join `vwmmf30approvedepthead` `d` on(`r`.`id` = `d`.`mmf30_id`)) left join `vwmmf30approveprochead` `h` on(`r`.`id` = `h`.`mmf30_id`)) left join `vwmmf30approvebuyer` `b` on(`r`.`id` = `b`.`mmf30_id`)) ;

-- Dumping structure for view oasys_dev.vwnewemp
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwnewemp`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwnewemp` AS select `tbl_employee`.`id` AS `id`,`tbl_employee`.`company_id` AS `company_id`,`tbl_employee`.`department_id` AS `department_id`,`tbl_employee`.`division_id` AS `division_id`,`tbl_employee`.`designation_id` AS `designation_id`,`tbl_employee`.`grade_id` AS `grade_id`,`tbl_employee`.`level_id` AS `level_id`,`tbl_employee`.`LoginName` AS `LoginName`,`tbl_employee`.`SAPID` AS `SAPID`,`tbl_employee`.`JoinDate` AS `JoinDate`,`tbl_employee`.`location_id` AS `location_id`,`tbl_employee`.`FullName` AS `FullName`,`tbl_employee`.`Address` AS `Address`,`tbl_employee`.`Gender` AS `Gender`,`tbl_employee`.`religion_id` AS `religion_id`,`tbl_employee`.`MaritalStatus` AS `MaritalStatus`,`tbl_employee`.`isActive` AS `isActive`,`tbl_employee`.`isInternationalStaff` AS `isInternationalStaff`,`tbl_employee`.`companycode` AS `companycode`,`tbl_employee`.`Remarks` AS `Remarks` from `tbl_employee` where `tbl_employee`.`id` > 1164 ;

-- Dumping structure for view oasys_dev.vwoasysappr
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwoasysappr`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwoasysappr` AS select row_number() over ( order by `e`.`FullName`) AS `row_num`,'oasys' AS `sistem`,`a`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`apt`.`Module` AS `module`,`apt`.`ApprovalType` AS `role`,`a`.`isActive` AS `active` from ((`tbl_approver` `a` left join `tbl_employee` `e` on(`a`.`employee_id` = `e`.`id`)) left join `tbl_approvaltype` `apt` on(`a`.`approvaltype_id` = `apt`.`id`)) where `a`.`isActive` = 1 and `apt`.`ApprovalType` not in ('Dept Head','Superior','Dept Head/ Sector Manager','Superior Verification') order by `e`.`FullName` ;

-- Dumping structure for view oasys_dev.vwpendingapproval
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwpendingapproval`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwpendingapproval` AS select 'Dayoff' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`dayoff_id` AS `dayoff_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`dayoff_id` AS `dayoff_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`dayoff_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_dayoffapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`dayoff_id` AS `dayoff_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_dayoffapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`dayoff_id` order by `td`.`dayoff_id`,`ta`.`sequence`) `s` on(`td`.`dayoff_id` = `s`.`dayoff_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_dayoffreq` `do` on(`td`.`dayoff_id` = `do`.`id`))) `x` group by `x`.`dayoff_id`,`x`.`employee_id` order by `x`.`dayoff_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'SPKL' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`spkl_id` AS `spkl_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`spkl_id` AS `spkl_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`spkl_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_spklapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`spkl_id` AS `spkl_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_spklapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`spkl_id`) `s` on(`td`.`spkl_id` = `s`.`spkl_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_spkl` `do` on(`td`.`spkl_id` = `do`.`id`))) `x` group by `x`.`spkl_id`,`x`.`employee_id` order by `x`.`spkl_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'SPKL_OT' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`spkl_id` AS `spkl_id`,`x`.`xid` AS `xid`,`x`.`tmsreqstatus` AS `tmsreqstatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`spkl_id` AS `spkl_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`spkl_id` AS `xid`,`do`.`TMSReqStatus` AS `tmsreqstatus` from (((`oasys`.`tbl_spklotapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`spkl_id` AS `spkl_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_spklotapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`spkl_id`) `s` on(`td`.`spkl_id` = `s`.`spkl_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_spkl` `do` on(`td`.`spkl_id` = `do`.`id`))) `x` group by `x`.`spkl_id`,`x`.`employee_id` order by `x`.`spkl_id`) `abc` where `abc`.`xid` is not null and `abc`.`tmsreqstatus` = '1' union all select 'TR' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`tr_id` AS `tr_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`tr_id` AS `tr_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`tr_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_trapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`tr_id` AS `tr_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_trapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`tr_id`) `s` on(`td`.`tr_id` = `s`.`tr_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_tr` `do` on(`td`.`tr_id` = `do`.`id`))) `x` group by `x`.`tr_id`,`x`.`employee_id` order by `x`.`tr_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'RFC' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`rfc_id` AS `rfc_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`rfc_id` AS `rfc_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`rfc_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_rfcapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`rfc_id` AS `rfc_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_rfcapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`rfc_id`) `s` on(`td`.`rfc_id` = `s`.`rfc_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_rfc` `do` on(`td`.`rfc_id` = `do`.`id`))) `x` group by `x`.`rfc_id`,`x`.`employee_id` order by `x`.`rfc_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'MMF28' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`mmf28_id` AS `mmf28_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`mmf28_id` AS `mmf28_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`mmf28_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_mmf28approval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`mmf28_id` AS `mmf28_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_mmf28approval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`mmf28_id`) `s` on(`td`.`mmf28_id` = `s`.`mmf28_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_mmf28` `do` on(`td`.`mmf28_id` = `do`.`id`))) `x` group by `x`.`mmf28_id`,`x`.`employee_id` order by `x`.`mmf28_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'MMF30' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`mmf30_id` AS `mmf30_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`mmf30_id` AS `mmf30_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`td`.`ProcComments` AS `ProcComments`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`mmf30_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_mmf30approval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`mmf30_id` AS `mmf30_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_mmf30approval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`mmf30_id`) `s` on(`td`.`mmf30_id` = `s`.`mmf30_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_mmf30` `do` on(`td`.`mmf30_id` = `do`.`id`))) `x` group by `x`.`mmf30_id`,`x`.`employee_id` order by `x`.`mmf30_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'AD' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`iteie_id` AS `iteie_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`iteie_id` AS `iteie_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`iteie_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_iteieapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`iteie_id` AS `iteie_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_iteieapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`iteie_id`) `s` on(`td`.`iteie_id` = `s`.`iteie_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_iteie` `do` on(`td`.`iteie_id` = `do`.`id`))) `x` group by `x`.`iteie_id`,`x`.`employee_id` order by `x`.`iteie_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'ITMail' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`itimail_id` AS `itimail_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`itimail_id` AS `itimail_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`itimail_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_itimailapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`itimail_id` AS `itimail_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_itimailapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`itimail_id`) `s` on(`td`.`itimail_id` = `s`.`itimail_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_itimail` `do` on(`td`.`itimail_id` = `do`.`id`))) `x` group by `x`.`itimail_id`,`x`.`employee_id` order by `x`.`itimail_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'ITShare' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`itsharef_id` AS `itsharef_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`itsharef_id` AS `itsharef_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`itsharef_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_itsharefapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`itsharef_id` AS `itsharef_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_itsharefapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`itsharef_id`) `s` on(`td`.`itsharef_id` = `s`.`itsharef_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_itsharef` `do` on(`td`.`itsharef_id` = `do`.`id`)) where `td`.`ApprovalStatus` = 0) `x` group by `x`.`itsharef_id`,`x`.`employee_id` order by `x`.`itsharef_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'Advance' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`advance_id` AS `advance_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`advance_id` AS `advance_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`advance_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_advanceapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`advance_id` AS `advance_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_advanceapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`advance_id`) `s` on(`td`.`advance_id` = `s`.`advance_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_advance` `do` on(`td`.`advance_id` = `do`.`id`))) `x` group by `x`.`advance_id`,`x`.`employee_id` order by `x`.`advance_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'Payment' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`advpayment_id` AS `advpayment_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`advpayment_id` AS `advpayment_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`advpayment_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_advpaymentapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`advpayment_id` AS `advpayment_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_advpaymentapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`advpayment_id`) `s` on(`td`.`advpayment_id` = `s`.`advpayment_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_advpayment` `do` on(`td`.`advpayment_id` = `do`.`id`))) `x` group by `x`.`advpayment_id`,`x`.`employee_id` order by `x`.`advpayment_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' union all select 'Expense' AS `module`,`abc`.`xid` AS `xid`,`abc`.`employee_id` AS `employee_id` from (select `x`.`advexpense_id` AS `advexpense_id`,`x`.`xid` AS `xid`,`x`.`RequestStatus` AS `requeststatus`,`x`.`employee_id` AS `employee_id` from (select `td`.`id` AS `id`,`td`.`advexpense_id` AS `advexpense_id`,`td`.`approver_id` AS `approver_id`,`td`.`ApprovalDate` AS `ApprovalDate`,`td`.`ApprovalStatus` AS `ApprovalStatus`,`td`.`Remarks` AS `Remarks`,`ta`.`sequence` AS `sequence`,`ta`.`employee_id` AS `employee_id`,`s`.`advexpense_id` AS `xid`,`do`.`RequestStatus` AS `RequestStatus` from (((`oasys`.`tbl_advexpenseapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) left join (select `td`.`advexpense_id` AS `advexpense_id`,min(`ta`.`sequence`) AS `sequence` from (`oasys`.`tbl_advexpenseapproval` `td` left join `oasys`.`tbl_approver` `ta` on(`td`.`approver_id` = `ta`.`id`)) where `td`.`ApprovalStatus` = 0 group by `td`.`advexpense_id`) `s` on(`td`.`advexpense_id` = `s`.`advexpense_id` and `ta`.`sequence` = `s`.`sequence`)) left join `oasys`.`tbl_advexpense` `do` on(`td`.`advexpense_id` = `do`.`id`))) `x` group by `x`.`advexpense_id`,`x`.`employee_id` order by `x`.`advexpense_id`) `abc` where `abc`.`xid` is not null and `abc`.`requeststatus` = '1' ;

-- Dumping structure for view oasys_dev.vwpendingbyemp
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwpendingbyemp`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwpendingbyemp` AS select `vwpendingapproval`.`module` AS `module`,`vwpendingapproval`.`employee_id` AS `employee_id`,count(`vwpendingapproval`.`xid`) AS `jml` from `oasys`.`vwpendingapproval` group by `vwpendingapproval`.`module`,`vwpendingapproval`.`employee_id` ;

-- Dumping structure for view oasys_dev.vwpendingreq
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwpendingreq`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwpendingreq` AS select 'Dayoff' AS `module`,`tbl_dayoffreq`.`employee_id` AS `employee_id`,count(0) AS `jml` from `tbl_dayoffreq` where `tbl_dayoffreq`.`RequestStatus` < 3 group by `tbl_dayoffreq`.`employee_id` union all select 'SPKL' AS `SPKL`,`tbl_spkl`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_spkl` where `tbl_spkl`.`RequestStatus` < 3 group by `tbl_spkl`.`employee_id` union all select 'SPKL_OT' AS `SPKL_OT`,`tbl_spkl`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_spkl` where `tbl_spkl`.`RequestStatus` = 3 and `tbl_spkl`.`TMSReqStatus` < 3 group by `tbl_spkl`.`employee_id` union all select 'TR' AS `TR`,`tbl_tr`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_tr` where `tbl_tr`.`RequestStatus` < 3 group by `tbl_tr`.`employee_id` union all select 'RFC' AS `RFC`,`tbl_rfc`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_rfc` where `tbl_rfc`.`RequestStatus` < 3 group by `tbl_rfc`.`employee_id` union all select 'MMF28' AS `MMF28`,`tbl_mmf28`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_mmf28` where `tbl_mmf28`.`RequestStatus` < 3 group by `tbl_mmf28`.`employee_id` union all select 'MMF30' AS `MMF30`,`tbl_mmf30`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_mmf30` where `tbl_mmf30`.`RequestStatus` < 3 group by `tbl_mmf30`.`employee_id` union all select 'AD' AS `AD`,`tbl_iteie`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_iteie` where `tbl_iteie`.`RequestStatus` < 3 group by `tbl_iteie`.`employee_id` union all select 'ITMail' AS `ITMail`,`tbl_itimail`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_itimail` where `tbl_itimail`.`RequestStatus` < 3 group by `tbl_itimail`.`employee_id` union all select 'ITShare' AS `ITShare`,`tbl_itsharef`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_itsharef` where `tbl_itsharef`.`RequestStatus` < 3 group by `tbl_itsharef`.`employee_id` union all select 'Advance' AS `Advance`,`tbl_advance`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_advance` where `tbl_advance`.`RequestStatus` < 3 group by `tbl_advance`.`employee_id` union all select 'Payment' AS `Payment`,`tbl_advpayment`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_advpayment` where `tbl_advpayment`.`RequestStatus` < 3 group by `tbl_advpayment`.`employee_id` union all select 'Expense' AS `Expense`,`tbl_advexpense`.`employee_id` AS `employee_id`,count(0) AS `COUNT(*)` from `tbl_advexpense` where `tbl_advexpense`.`RequestStatus` < 3 group by `tbl_advexpense`.`employee_id` ;

-- Dumping structure for view oasys_dev.vwrfcapprovebufc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovebufc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovebufc` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '10' ;

-- Dumping structure for view oasys_dev.vwrfcapprovebuhead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovebuhead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovebuhead` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '11' ;

-- Dumping structure for view oasys_dev.vwrfcapprovecadbu
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovecadbu`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovecadbu` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '7' ;

-- Dumping structure for view oasys_dev.vwrfcapprovecadkf
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovecadkf`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovecadkf` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '8' ;

-- Dumping structure for view oasys_dev.vwrfcapprovecpu
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovecpu`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovecpu` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '12' ;

-- Dumping structure for view oasys_dev.vwrfcapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovedepthead` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '6' ;

-- Dumping structure for view oasys_dev.vwrfcapprovehrd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovehrd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovehrd` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '9' ;

-- Dumping structure for view oasys_dev.vwrfcapprovehrkf
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovehrkf`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovehrkf` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '15' ;

-- Dumping structure for view oasys_dev.vwrfcapprovekffc
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovekffc`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovekffc` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '13' ;

-- Dumping structure for view oasys_dev.vwrfcapprovemdkf
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcapprovemdkf`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcapprovemdkf` AS select `a`.`id` AS `id`,`a`.`rfc_id` AS `rfc_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_rfcapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '14' ;

-- Dumping structure for view oasys_dev.vwrfcreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcreport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`CompanyCode` AS `CompanyCode`,`r`.`depthead` AS `depthead`,`r`.`RFCNo` AS `RFCNo`,`r`.`activity_id` AS `activity_id`,`r`.`RFCType` AS `RFCType`,`r`.`isProjectCapex` AS `isProjectCapex`,`r`.`OldContractNo` AS `OldContractNo`,`r`.`PeriodStart` AS `PeriodStart`,`r`.`PeriodEnd` AS `PeriodEnd`,`r`.`RateType` AS `RateType`,`r`.`Rate` AS `Rate`,`r`.`contractor_id` AS `contractor_id`,`r`.`contractor_id2` AS `contractor_id2`,`r`.`PaymentTerm` AS `PaymentTerm`,`r`.`ContractNo` AS `ContractNo`,`r`.`ContractDate` AS `ContractDate`,`r`.`PRNo` AS `PRNo`,`r`.`PRCreatedDate` AS `PRCreatedDate`,`r`.`PONo` AS `PONo`,`r`.`POCreatedDate` AS `POCreatedDate`,`r`.`contract_id` AS `contract_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`CapexNo` AS `CapexNo`,`r`.`CapexAmmount` AS `CapexAmmount`,`r`.`CapexSpent` AS `CapexSpent`,`r`.`CapexBalance` AS `CapexBalance`,`r`.`RFCAmmount` AS `RFCAmmount`,`r`.`Balance` AS `Balance`,`r`.`Remarks` AS `Remarks`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprCADBUName` AS `ApprCADBUName`,`r`.`ApprCADBUStatus` AS `ApprCADBUStatus`,`r`.`ApprCADBUDate` AS `ApprCADBUDate`,`r`.`ApprHRDName` AS `ApprHRDName`,`r`.`ApprHRDStatus` AS `ApprHRDStatus`,`r`.`ApprHRDDate` AS `ApprHRDDate`,`r`.`ApprHRKFName` AS `ApprHRKFName`,`r`.`ApprHRKFStatus` AS `ApprHRKFStatus`,`r`.`ApprHRKFDate` AS `ApprHRKFDate`,`r`.`ApprCADKFName` AS `ApprCADKFName`,`r`.`ApprCADKFStatus` AS `ApprCADKFStatus`,`r`.`ApprCADKFDate` AS `ApprCADKFDate`,`r`.`ApprFCBUName` AS `ApprFCBUName`,`r`.`ApprFCBUStatus` AS `ApprFCBUStatus`,`r`.`ApprFCBUDate` AS `ApprFCBUDate`,`r`.`ApprBUHeadName` AS `ApprBUHeadName`,`r`.`ApprBUHeadStatus` AS `ApprBUHeadStatus`,`r`.`ApprBUHeadDate` AS `ApprBUHeadDate`,`r`.`ApprCPUName` AS `ApprCPUName`,`r`.`ApprCPUStatus` AS `ApprCPUStatus`,`r`.`ApprCPUDate` AS `ApprCPUDate`,`r`.`ApprKFFCName` AS `ApprKFFCName`,`r`.`ApprKFFCStatus` AS `ApprKFFCStatus`,`r`.`ApprKFFCDate` AS `ApprKFFCDate`,`r`.`ApprMDKFName` AS `ApprMDKFName`,`r`.`ApprMDKFStatus` AS `ApprMDKFStatus`,`r`.`ApprMDKFDate` AS `ApprMDKFDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `Department`,`a`.`ActivityDescr` AS `KindOfContract`,`c1`.`ContractorName` AS `ContractorRecom1`,`c2`.`ContractorName` AS `ContractorRecom2`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprCADBUDate` is null and `r`.`ApprCADBUName` is not null then ' CAD BU' when `r`.`ApprCADKFDate` is null and `r`.`ApprCADKFName` is not null then ' CAD HO' when `r`.`ApprHRDDate` is null and `r`.`ApprHRDName` is not null then ' HR BU' when `r`.`ApprHRKFDate` is null and `r`.`ApprHRKFName` is not null then ' HR HO' when `r`.`ApprFCBUDate` is null and `r`.`ApprFCBUName` is not null then ' FC BU' when `r`.`ApprBUHeadDate` is null and `r`.`ApprBUHeadName` is not null then ' BU Head' when `r`.`ApprCPUDate` is null and `r`.`ApprCPUName` is not null then ' CPU' when `r`.`ApprKFFCDate` is null and `r`.`ApprKFFCName` is not null then ' FC HO' when `r`.`ApprMDKFDate` is null and `r`.`ApprMDKFName` is not null then ' MD' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprCADBUDate` is null and `r`.`ApprCADBUName` is not null then `r`.`ApprCADBUName` when `r`.`ApprCADKFDate` is null and `r`.`ApprCADKFName` is not null then `r`.`ApprCADKFName` when `r`.`ApprHRDDate` is null and `r`.`ApprHRDName` is not null then `r`.`ApprHRDName` when `r`.`ApprHRKFDate` is null and `r`.`ApprHRKFName` is not null then `r`.`ApprHRKFName` when `r`.`ApprFCBUDate` is null and `r`.`ApprFCBUName` is not null then `r`.`ApprFCBUName` when `r`.`ApprBUHeadDate` is null and `r`.`ApprBUHeadName` is not null then `r`.`ApprBUHeadName` when `r`.`ApprCPUDate` is null and `r`.`ApprCPUName` is not null then `r`.`ApprCPUName` when `r`.`ApprKFFCDate` is null and `r`.`ApprKFFCName` is not null then `r`.`ApprKFFCName` when `r`.`ApprMDKFDate` is null and `r`.`ApprMDKFName` is not null then `r`.`ApprMDKFName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from (((((`vwrfcreportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) left join `tbl_rfcactivity` `a` on(`r`.`activity_id` = `a`.`id`)) left join `tbl_rfccontractor` `c1` on(`r`.`contractor_id` = `c1`.`id`)) left join `tbl_rfccontractor` `c2` on(`r`.`contractor_id` = `c2`.`id`)) ;

-- Dumping structure for view oasys_dev.vwrfcreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwrfcreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwrfcreportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`employee_id` AS `employee_id`,`r`.`CompanyCode` AS `CompanyCode`,`r`.`depthead` AS `depthead`,`r`.`RFCNo` AS `RFCNo`,`r`.`activity_id` AS `activity_id`,`r`.`RFCType` AS `RFCType`,`r`.`isProjectCapex` AS `isProjectCapex`,`r`.`OldContractNo` AS `OldContractNo`,`r`.`PeriodStart` AS `PeriodStart`,`r`.`PeriodEnd` AS `PeriodEnd`,`r`.`RateType` AS `RateType`,`r`.`Rate` AS `Rate`,`r`.`contractor_id` AS `contractor_id`,`r`.`contractor_id2` AS `contractor_id2`,`r`.`PaymentTerm` AS `PaymentTerm`,`r`.`ContractNo` AS `ContractNo`,`r`.`ContractDate` AS `ContractDate`,`r`.`PRNo` AS `PRNo`,`r`.`PRCreatedDate` AS `PRCreatedDate`,`r`.`PONo` AS `PONo`,`r`.`POCreatedDate` AS `POCreatedDate`,`r`.`contract_id` AS `contract_id`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`CapexNo` AS `CapexNo`,`r`.`CapexAmmount` AS `CapexAmmount`,`r`.`CapexSpent` AS `CapexSpent`,`r`.`CapexBalance` AS `CapexBalance`,`r`.`RFCAmmount` AS `RFCAmmount`,`r`.`Balance` AS `Balance`,`r`.`Remarks` AS `Remarks`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`c`.`fullname` AS `ApprCADBUName`,case when `c`.`ApprovalStatus` = '0' then 'Waiting Approval' when `c`.`ApprovalStatus` = '1' then 'Rework' when `c`.`ApprovalStatus` = '2' then 'Approved' when `c`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprCADBUStatus`,case when `c`.`ApprovalStatus` not in ('0','4') then `c`.`ApprovalDate` else NULL end AS `ApprCADBUDate`,`h`.`fullname` AS `ApprHRDName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHRDStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHRDDate`,`hk`.`fullname` AS `ApprHRKFName`,case when `hk`.`ApprovalStatus` = '0' then 'Waiting Approval' when `hk`.`ApprovalStatus` = '1' then 'Rework' when `hk`.`ApprovalStatus` = '2' then 'Approved' when `hk`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHRKFStatus`,case when `hk`.`ApprovalStatus` not in ('0','4') then `hk`.`ApprovalDate` else NULL end AS `ApprHRKFDate`,`ck`.`fullname` AS `ApprCADKFName`,case when `ck`.`ApprovalStatus` = '0' then 'Waiting Approval' when `ck`.`ApprovalStatus` = '1' then 'Rework' when `ck`.`ApprovalStatus` = '2' then 'Approved' when `ck`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprCADKFStatus`,case when `ck`.`ApprovalStatus` not in ('0','4') then `ck`.`ApprovalDate` else NULL end AS `ApprCADKFDate`,`fb`.`fullname` AS `ApprFCBUName`,case when `fb`.`ApprovalStatus` = '0' then 'Waiting Approval' when `fb`.`ApprovalStatus` = '1' then 'Rework' when `fb`.`ApprovalStatus` = '2' then 'Approved' when `fb`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprFCBUStatus`,case when `fb`.`ApprovalStatus` not in ('0','4') then `fb`.`ApprovalDate` else NULL end AS `ApprFCBUDate`,`bh`.`fullname` AS `ApprBUHeadName`,case when `bh`.`ApprovalStatus` = '0' then 'Waiting Approval' when `bh`.`ApprovalStatus` = '1' then 'Rework' when `bh`.`ApprovalStatus` = '2' then 'Approved' when `bh`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprBUHeadStatus`,case when `bh`.`ApprovalStatus` not in ('0','4') then `bh`.`ApprovalDate` else NULL end AS `ApprBUHeadDate`,`cpu`.`fullname` AS `ApprCPUName`,case when `cpu`.`ApprovalStatus` = '0' then 'Waiting Approval' when `cpu`.`ApprovalStatus` = '1' then 'Rework' when `cpu`.`ApprovalStatus` = '2' then 'Approved' when `cpu`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprCPUStatus`,case when `cpu`.`ApprovalStatus` not in ('0','4') then `cpu`.`ApprovalDate` else NULL end AS `ApprCPUDate`,`kfc`.`fullname` AS `ApprKFFCName`,case when `kfc`.`ApprovalStatus` = '0' then 'Waiting Approval' when `kfc`.`ApprovalStatus` = '1' then 'Rework' when `kfc`.`ApprovalStatus` = '2' then 'Approved' when `kfc`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprKFFCStatus`,case when `kfc`.`ApprovalStatus` not in ('0','4') then `kfc`.`ApprovalDate` else NULL end AS `ApprKFFCDate`,`m`.`fullname` AS `ApprMDKFName`,case when `m`.`ApprovalStatus` = '0' then 'Waiting Approval' when `m`.`ApprovalStatus` = '1' then 'Rework' when `m`.`ApprovalStatus` = '2' then 'Approved' when `m`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprMDKFStatus`,case when `m`.`ApprovalStatus` not in ('0','4') then `m`.`ApprovalDate` else NULL end AS `ApprMDKFDate` from ((((((((((`tbl_rfc` `r` left join `vwrfcapprovedepthead` `d` on(`r`.`id` = `d`.`rfc_id`)) left join `vwrfcapprovecadbu` `c` on(`r`.`id` = `c`.`rfc_id`)) left join `vwrfcapprovecadkf` `ck` on(`r`.`id` = `ck`.`rfc_id`)) left join `vwrfcapprovehrd` `h` on(`r`.`id` = `h`.`rfc_id`)) left join `vwrfcapprovehrkf` `hk` on(`r`.`id` = `hk`.`rfc_id`)) left join `vwrfcapprovebufc` `fb` on(`r`.`id` = `fb`.`rfc_id`)) left join `vwrfcapprovebuhead` `bh` on(`r`.`id` = `bh`.`rfc_id`)) left join `vwrfcapprovecpu` `cpu` on(`r`.`id` = `cpu`.`rfc_id`)) left join `vwrfcapprovekffc` `kfc` on(`r`.`id` = `kfc`.`rfc_id`)) left join `vwrfcapprovemdkf` `m` on(`r`.`id` = `m`.`rfc_id`)) ;

-- Dumping structure for view oasys_dev.vwspklapppending
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklapppending`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklapppending` AS select `a`.`id` AS `id`,`a`.`spkl_id` AS `spkl_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`a`.`employee_id` AS `employee_id`,`a`.`fullname` AS `fullname`,`a`.`departmentname` AS `departmentname`,`a`.`approvaltype_id` AS `approvaltype_id`,`a`.`ApprovalType` AS `ApprovalType`,`a`.`sequence` AS `sequence`,`a`.`isFinal` AS `isFinal` from (`vwspklapproval` `a` left join `vwspklapprovalsub` `b` on(`a`.`spkl_id` = `b`.`spkl_id` and `a`.`sequence` = `b`.`sequence`)) where `b`.`spkl_id` is not null ;

-- Dumping structure for view oasys_dev.vwspklapproval
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklapproval`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklapproval` AS select `a`.`id` AS `id`,`a`.`spkl_id` AS `spkl_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`t`.`ApprovalType` AS `ApprovalType`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from ((((`tbl_spklapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_approvaltype` `t` on(`p`.`approvaltype_id` = `t`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwspklapprovalsub
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklapprovalsub`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklapprovalsub` AS select min(`vwspklapproval`.`sequence`) AS `sequence`,`vwspklapproval`.`spkl_id` AS `spkl_id` from `vwspklapproval` where `vwspklapproval`.`ApprovalStatus` = 0 group by `vwspklapproval`.`spkl_id` ;

-- Dumping structure for view oasys_dev.vwspklotapppending
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklotapppending`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklotapppending` AS select `a`.`id` AS `id`,`a`.`spkl_id` AS `spkl_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`a`.`employee_id` AS `employee_id`,`a`.`fullname` AS `fullname`,`a`.`departmentname` AS `departmentname`,`a`.`approvaltype_id` AS `approvaltype_id`,`a`.`ApprovalType` AS `ApprovalType`,`a`.`sequence` AS `sequence`,`a`.`isFinal` AS `isFinal` from (`vwspklotapproval` `a` left join `vwspklotapprovalsub` `b` on(`a`.`spkl_id` = `b`.`spkl_id` and `a`.`sequence` = `b`.`sequence`)) where `b`.`spkl_id` is not null ;

-- Dumping structure for view oasys_dev.vwspklotapproval
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklotapproval`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklotapproval` AS select `a`.`id` AS `id`,`a`.`spkl_id` AS `spkl_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`t`.`ApprovalType` AS `ApprovalType`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from ((((`tbl_spklotapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_approvaltype` `t` on(`p`.`approvaltype_id` = `t`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwspklotapprovalsub
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklotapprovalsub`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklotapprovalsub` AS select min(`vwspklotapproval`.`sequence`) AS `sequence`,`vwspklotapproval`.`spkl_id` AS `spkl_id` from `vwspklotapproval` where `vwspklotapproval`.`ApprovalStatus` = 0 group by `vwspklotapproval`.`spkl_id` ;

-- Dumping structure for view oasys_dev.vwspklreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwspklreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwspklreport` AS select `s`.`id` AS `id`,`s`.`SPKLNo` AS `SPKLNo`,`s`.`employee_id` AS `employee_id`,`s`.`CreatedDate` AS `CreatedDate`,`s`.`DateWork` AS `DateWork`,`s`.`RequestStatus` AS `RequestStatus`,`s`.`DeptHead` AS `DeptHead`,`s`.`ApprovedDoc` AS `ApprovedDoc`,`s`.`TMSReqDate` AS `TMSReqDate`,`s`.`TMSReqBy` AS `TMSReqBy`,`s`.`TMSReqStatus` AS `TMSReqStatus`,`s`.`Superior` AS `Superior`,`s`.`ApprovedTMSDoc` AS `ApprovedTMSDoc`,`s`.`isExceedPlan` AS `isExceedPlan`,`s`.`ApprovalStep` AS `ApprovalStep`,`s`.`isMoreThan2hours` AS `isMoreThan2hours`,`s`.`Remarks` AS `Remarks`,case when `s`.`RequestStatus` = 0 then 'Saved as Draft' when `s`.`RequestStatus` = 1 then concat('Waiting Approval ',`sp`.`ApprovalType`) when `s`.`RequestStatus` = 2 then 'Require Rework' when `s`.`RequestStatus` = 3 then 'Approved' when `s`.`RequestStatus` = 4 then 'Rejected' else '' end AS `SPKLStatus`,case when `s`.`RequestStatus` < 3 then 'Pending SPKL Approval' when `s`.`RequestStatus` = 4 then 'SPKL Rejected' when `s`.`TMSReqStatus` = 0 then 'Not Submitted' when `s`.`TMSReqStatus` = 1 then concat('Waiting Approval ',`sop`.`ApprovalType`) when `s`.`TMSReqStatus` = 2 then 'Require Rework' when `s`.`TMSReqStatus` = 3 then 'Approved' when `s`.`TMSReqStatus` = 4 then 'Rejected' else '' end AS `OTStatus`,case when `s`.`RequestStatus` = 1 then `sp`.`fullname` when `s`.`RequestStatus` = 3 and `s`.`TMSReqStatus` = 1 then `sop`.`fullname` when `s`.`TMSReqStatus` = 3 then 'Approved' else `e`.`FullName` end AS `PersonHolding`,`sp`.`fullname` AS `SPKLApprName`,`sp`.`departmentname` AS `SPKLApprDept`,`sp`.`ApprovalType` AS `SPKLApprType`,`sop`.`fullname` AS `OTApprName`,`sop`.`departmentname` AS `OTApprDept`,`sop`.`ApprovalType` AS `OTApprType` from (((`tbl_spkl` `s` left join `vwspklapppending` `sp` on(`s`.`id` = `sp`.`spkl_id`)) left join `vwspklotapppending` `sop` on(`s`.`id` = `sop`.`spkl_id`)) left join `tbl_employee` `e` on(`s`.`employee_id` = `e`.`id`)) ;

-- Dumping structure for view oasys_dev.vwtrapprovedepthead
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrapprovedepthead`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrapprovedepthead` AS select `a`.`id` AS `id`,`a`.`tr_id` AS `tr_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_trapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '16' ;

-- Dumping structure for view oasys_dev.vwtrapprovehrbu
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrapprovehrbu`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrapprovehrbu` AS select `a`.`id` AS `id`,`a`.`tr_id` AS `tr_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_trapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '17' ;

-- Dumping structure for view oasys_dev.vwtrapprovehrho
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrapprovehrho`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrapprovehrho` AS select `a`.`id` AS `id`,`a`.`tr_id` AS `tr_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_trapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '18' ;

-- Dumping structure for view oasys_dev.vwtrapprovemd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrapprovemd`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrapprovemd` AS select `a`.`id` AS `id`,`a`.`tr_id` AS `tr_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_trapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '19' ;

-- Dumping structure for view oasys_dev.vwtrapprovesuperior
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrapprovesuperior`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrapprovesuperior` AS select `a`.`id` AS `id`,`a`.`tr_id` AS `tr_id`,`a`.`approver_id` AS `approver_id`,`a`.`ApprovalDate` AS `ApprovalDate`,`a`.`ApprovalStatus` AS `ApprovalStatus`,`a`.`Remarks` AS `Remarks`,`p`.`employee_id` AS `employee_id`,`e`.`FullName` AS `fullname`,`d`.`DepartmentName` AS `departmentname`,`p`.`approvaltype_id` AS `approvaltype_id`,`p`.`sequence` AS `sequence`,`p`.`isFinal` AS `isFinal` from (((`tbl_trapproval` `a` left join `tbl_approver` `p` on(`a`.`approver_id` = `p`.`id`)) left join `tbl_employee` `e` on(`p`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) where `p`.`approvaltype_id` = '43' ;

-- Dumping structure for view oasys_dev.vwtrreport
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrreport`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrreport` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`CreatedBy` AS `CreatedBy`,`r`.`employee_id` AS `employee_id`,`r`.`TravelCategory` AS `TravelCategory`,`r`.`isLandTransport` AS `isLandTransport`,`r`.`isAirTransport` AS `isAirTransport`,`r`.`isPersonalVehicle` AS `isPersonalVehicle`,`r`.`isPoolCar` AS `isPoolCar`,`r`.`isDropOffOnly` AS `isDropOffOnly`,`r`.`isUntilJobFinish` AS `isUntilJobFinish`,`r`.`JobFinishDate` AS `JobFinishDate`,`r`.`isByTrain` AS `isByTrain`,`r`.`isOther` AS `isOther`,`r`.`OtherLandTransportDesc` AS `OtherLandTransportDesc`,`r`.`isCommercialAirline` AS `isCommercialAirline`,`r`.`isCompanyAircraft` AS `isCompanyAircraft`,`r`.`DeptHead` AS `DeptHead`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`TravelCashAdvancePurpose` AS `TravelCashAdvancePurpose`,`r`.`SPPDDays` AS `SPPDDays`,`r`.`SPPDAmmount` AS `SPPDAmmount`,`r`.`TaxiDays` AS `TaxiDays`,`r`.`TaxiAmmount` AS `TaxiAmmount`,`r`.`AccommodationDays` AS `AccommodationDays`,`r`.`AccommodationAmmount` AS `AccommodationAmmount`,`r`.`TelephoneDays` AS `TelephoneDays`,`r`.`TelephoneAmmount` AS `TelephoneAmmount`,`r`.`OtherIDRDays` AS `OtherIDRDays`,`r`.`OtherIDRAmmount` AS `OtherIDRAmmount`,`r`.`PerdiemAmmount` AS `PerdiemAmmount`,`r`.`OtherUSDAmmount` AS `OtherUSDAmmount`,`r`.`TotalAdvanceIDR` AS `TotalAdvanceIDR`,`r`.`TotalAdvanceUSD` AS `TotalAdvanceUSD`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`Remarks` AS `Remarks`,`r`.`ApprDeptHeadName` AS `ApprDeptHeadName`,`r`.`ApprDeptHeadStatus` AS `ApprDeptHeadStatus`,`r`.`ApprDeptHeadDate` AS `ApprDeptHeadDate`,`r`.`ApprHRDName` AS `ApprHRDName`,`r`.`ApprHRDStatus` AS `ApprHRDStatus`,`r`.`ApprHRDDate` AS `ApprHRDDate`,`r`.`ApprHRKFName` AS `ApprHRKFName`,`r`.`ApprHRKFStatus` AS `ApprHRKFStatus`,`r`.`ApprHRKFDate` AS `ApprHRKFDate`,`r`.`ApprMDKFName` AS `ApprMDKFName`,`r`.`ApprMDKFStatus` AS `ApprMDKFStatus`,`r`.`ApprMDKFDate` AS `ApprMDKFDate`,`e`.`FullName` AS `RequestBy`,`d`.`DepartmentName` AS `Department`,case when `r`.`RequestStatus` = '0' then 'Saved as Draft' when `r`.`RequestStatus` = '1' then concat('Waiting Approval',case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then ' Superior' when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then ' Dept Head' when `r`.`ApprHRDDate` is null and `r`.`ApprHRDName` is not null then ' HRD BU' when `r`.`ApprHRKFDate` is null and `r`.`ApprHRKFName` is not null then ' HRD HO' when `r`.`ApprMDKFDate` is null and `r`.`ApprMDKFName` is not null then ' Deputy MD' else '' end) when `r`.`RequestStatus` = '2' then 'Require Rework' when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `LastStatus`,case when `r`.`RequestStatus` = '0' then `e`.`FullName` when `r`.`RequestStatus` = '1' then case when `r`.`ApprSuperiorDate` is null and `r`.`ApprSuperiorName` is not null then `r`.`ApprSuperiorName` when `r`.`ApprDeptHeadDate` is null and `r`.`ApprDeptHeadName` is not null then `r`.`ApprDeptHeadName` when `r`.`ApprHRDDate` is null and `r`.`ApprHRDName` is not null then `r`.`ApprHRDName` when `r`.`ApprHRKFDate` is null and `r`.`ApprHRKFName` is not null then `r`.`ApprHRKFName` when `r`.`ApprMDKFDate` is null and `r`.`ApprMDKFName` is not null then `r`.`ApprMDKFName` else '' end when `r`.`RequestStatus` = '2' then `e`.`FullName` when `r`.`RequestStatus` = '3' then 'Approved' when `r`.`RequestStatus` = '4' then 'Rejected' else '' end AS `PersonHolding` from ((`vwtrreportsubquery` `r` left join `tbl_employee` `e` on(`r`.`employee_id` = `e`.`id`)) left join `tbl_department` `d` on(`e`.`department_id` = `d`.`id`)) ;

-- Dumping structure for view oasys_dev.vwtrreportsubquery
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwtrreportsubquery`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwtrreportsubquery` AS select `r`.`id` AS `id`,`r`.`CreatedDate` AS `CreatedDate`,`r`.`CreatedBy` AS `CreatedBy`,`r`.`employee_id` AS `employee_id`,`r`.`TravelCategory` AS `TravelCategory`,`r`.`isLandTransport` AS `isLandTransport`,`r`.`isAirTransport` AS `isAirTransport`,`r`.`isPersonalVehicle` AS `isPersonalVehicle`,`r`.`isPoolCar` AS `isPoolCar`,`r`.`isDropOffOnly` AS `isDropOffOnly`,`r`.`isUntilJobFinish` AS `isUntilJobFinish`,`r`.`JobFinishDate` AS `JobFinishDate`,`r`.`isByTrain` AS `isByTrain`,`r`.`isOther` AS `isOther`,`r`.`OtherLandTransportDesc` AS `OtherLandTransportDesc`,`r`.`isCommercialAirline` AS `isCommercialAirline`,`r`.`isCompanyAircraft` AS `isCompanyAircraft`,`r`.`DeptHead` AS `DeptHead`,`r`.`RequestStatus` AS `RequestStatus`,`r`.`TravelCashAdvancePurpose` AS `TravelCashAdvancePurpose`,`r`.`SPPDDays` AS `SPPDDays`,`r`.`SPPDAmmount` AS `SPPDAmmount`,`r`.`TaxiDays` AS `TaxiDays`,`r`.`TaxiAmmount` AS `TaxiAmmount`,`r`.`AccommodationDays` AS `AccommodationDays`,`r`.`AccommodationAmmount` AS `AccommodationAmmount`,`r`.`TelephoneDays` AS `TelephoneDays`,`r`.`TelephoneAmmount` AS `TelephoneAmmount`,`r`.`OtherIDRDays` AS `OtherIDRDays`,`r`.`OtherIDRAmmount` AS `OtherIDRAmmount`,`r`.`PerdiemAmmount` AS `PerdiemAmmount`,`r`.`OtherUSDAmmount` AS `OtherUSDAmmount`,`r`.`TotalAdvanceIDR` AS `TotalAdvanceIDR`,`r`.`TotalAdvanceUSD` AS `TotalAdvanceUSD`,`r`.`ApprovedDoc` AS `ApprovedDoc`,`r`.`Remarks` AS `Remarks`,`s`.`fullname` AS `ApprSuperiorName`,case when `s`.`ApprovalStatus` = '0' then 'Waiting Approval' when `s`.`ApprovalStatus` = '1' then 'Rework' when `s`.`ApprovalStatus` = '2' then 'Approved' when `s`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprSuperiorStatus`,case when `s`.`ApprovalStatus` not in ('0','4') then `s`.`ApprovalDate` else NULL end AS `ApprSuperiorDate`,`d`.`fullname` AS `ApprDeptHeadName`,case when `d`.`ApprovalStatus` = '0' then 'Waiting Approval' when `d`.`ApprovalStatus` = '1' then 'Rework' when `d`.`ApprovalStatus` = '2' then 'Approved' when `d`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprDeptHeadStatus`,case when `d`.`ApprovalStatus` not in ('0','4') then `d`.`ApprovalDate` else NULL end AS `ApprDeptHeadDate`,`h`.`fullname` AS `ApprHRDName`,case when `h`.`ApprovalStatus` = '0' then 'Waiting Approval' when `h`.`ApprovalStatus` = '1' then 'Rework' when `h`.`ApprovalStatus` = '2' then 'Approved' when `h`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHRDStatus`,case when `h`.`ApprovalStatus` not in ('0','4') then `h`.`ApprovalDate` else NULL end AS `ApprHRDDate`,`hk`.`fullname` AS `ApprHRKFName`,case when `hk`.`ApprovalStatus` = '0' then 'Waiting Approval' when `hk`.`ApprovalStatus` = '1' then 'Rework' when `hk`.`ApprovalStatus` = '2' then 'Approved' when `hk`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprHRKFStatus`,case when `hk`.`ApprovalStatus` not in ('0','4') then `hk`.`ApprovalDate` else NULL end AS `ApprHRKFDate`,`m`.`fullname` AS `ApprMDKFName`,case when `m`.`ApprovalStatus` = '0' then 'Waiting Approval' when `m`.`ApprovalStatus` = '1' then 'Rework' when `m`.`ApprovalStatus` = '2' then 'Approved' when `m`.`ApprovalStatus` = '3' then 'Rejected' else '' end AS `ApprMDKFStatus`,case when `m`.`ApprovalStatus` not in ('0','4') then `m`.`ApprovalDate` else NULL end AS `ApprMDKFDate` from (((((`tbl_tr` `r` left join `vwtrapprovesuperior` `s` on(`r`.`id` = `s`.`tr_id`)) left join `vwtrapprovedepthead` `d` on(`r`.`id` = `d`.`tr_id`)) left join `vwtrapprovehrbu` `h` on(`r`.`id` = `h`.`tr_id`)) left join `vwtrapprovehrho` `hk` on(`r`.`id` = `hk`.`tr_id`)) left join `vwtrapprovemd` `m` on(`r`.`id` = `m`.`tr_id`)) ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
