<?xml version="1.0"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">

  <xsl:strip-space elements="*" />
  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes"/>

  <!-- RDF back links to HTML -->
<xsl:variable name="apos">'</xsl:variable>

  <xsl:template match="/rdf:RDF">
    <p>
      <h5>Is used by:</h5>
      <table class="rdf rdf_main">
        <tr>
          <th>Property</th>
          <th>Value</th>
        </tr>
        <xsl:for-each select="*">
          <xsl:variable name="target" select="@rdf:about"/>
          <xsl:for-each select="*">
            <tr>
              <td>
              Is
              <a>
                <xsl:attribute name="href">
                  <xsl:value-of select="concat(namespace-uri(),local-name())"/>
                  </xsl:attribute>
                  <xsl:value-of select="name()"/>
                </a>
              of
            </td>
              <td>
                <a>
                  <xsl:attribute name="href">
                    <xsl:value-of select="$target"/>
                  </xsl:attribute>
                 <xsl:attribute name="property">
                  <xsl:value-of select="concat(namespace-uri(),local-name())"/>
                 </xsl:attribute>
                  <xsl:value-of select="$target"/>
                </a>
                <xsl:if test="not(starts-with($target,'http'))">
                  &#160;&#160;&#160;
                  <a class="load_entry"><xsl:attribute name="href">
                    <xsl:value-of select="concat('javascript:ajax_load_entry(',$apos,$target,$apos,')')"/>
                  </xsl:attribute>More...</a>
                  <div style="display:none;">
                    <xsl:attribute name="id">
                      <xsl:value-of select="concat('la_',$target)"/>
                    </xsl:attribute>
                  </div>
                </xsl:if>
              </td>
            </tr>
          </xsl:for-each>
        </xsl:for-each>
      </table>
    </p>
  </xsl:template>
</xsl:stylesheet>
