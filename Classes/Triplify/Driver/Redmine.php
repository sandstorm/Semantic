<?php
/*                                                                        *
 * This script belongs to the "SandstormMedia.Semantic" package.          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3          *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * © 2011 Sandstorm Media UG (haftungsbeschränkt)                         *
 *        http://sandstorm-media.de                                       */
namespace SandstormMedia\Semantic\Triplify\Driver;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 */
class Redmine extends \SandstormMedia\Semantic\Triplify\AbstractDriver {

	/**
	 * MASTER CONFIGURATION
	 */
	protected $objects = array(
		'project' => '{BASEURI}/projects/{_id}',
		'issue' => '{BASEURI}/issues/{_id}'
	);

	/**
	 * CONFIGURATION OF NAMED GRAPHS THAT SHOULD BE CREATED
	 */
	protected $namedGraphs = array(
		'{BASEURI}/projects/{_id}.rdf' => array(
			'doap:project' => array(
				'_only' => array(
					'rdf:type',
					'dcterms:title',
					'sioc:content',
					'dcterms:modified',
					'dcterms:created',
					'dbug:issue',
				),
				'_descend' => array(
					'dbug:issue' => array(
						'_only' => array(
							'rdf:type',
							'rdfs:comment'
						)
					)
				)
			)
		)
	);

	/**
	 * PROJECT
	 */
	protected $projectType = 'doap:project';
	protected $projectQueries = array(
		// Project Metadata
		"SELECT p.identifier AS _id, p.name AS 'dcterms:title',
				p.description AS 'sioc:content',
				p.updated_on AS 'dcterms:modified->asDateTime()',
				p.created_on AS 'dcterms:created->asDateTime()'
			FROM projects p WHERE p.is_public=1",
		// Project -> Issue
		"SELECT p.identifier AS _id,
				i.id AS 'dbug:issue->issue'
			FROM issues i, projects p WHERE p.id = i.project_id"
	);

	/**
	 * ISSUE
	 */
	protected $issueType = 'dbug:issue';
	protected $issueQueries = array(
		// Issue Metadata
		"SELECT i.id AS _id,
				i.subject AS 'rdfs:label',
				i.description AS 'rdfs:comment'
			FROM issues i"
	);
}
?>