<?php
namespace TYPO3\FLOW3\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20111219184045 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE sandstormmedia_semantic_linkification_domain_model_externa_38457 (flow3_persistence_identifier VARCHAR(40) NOT NULL, objectuuid VARCHAR(255) DEFAULT NULL, propertyname VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE sandstormmedia_semantic_linkification_domain_model_textann_6d404 (flow3_persistence_identifier VARCHAR(40) NOT NULL, objectuuid VARCHAR(255) DEFAULT NULL, propertyname VARCHAR(255) DEFAULT NULL, annotations LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE sandstormmedia_semantic_linkification_domain_model_externa_38457");
		$this->addSql("DROP TABLE sandstormmedia_semantic_linkification_domain_model_textann_6d404");
	}
}

?>