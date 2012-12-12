<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:sparql="http://www.w3.org/2005/sparql-results#">

  <xsl:strip-space elements="*" />
  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes"/>
  
<xsl:template match="/">
  <table class="sparql">
    <thead>
      <tr>
        <xsl:for-each select="//sparql:variable">
            <th><xsl:value-of select="@name"/></th>
        </xsl:for-each>
     </tr>
    </thead>
    <xsl:for-each select="//sparql:result">
      <tr>
        <xsl:for-each select="sparql:binding">
          <xsl:choose select="*">
            <xsl:when test="sparql:uri">
              <td><a>
                <xsl:attribute name="href">
                  <xsl:value-of select="sparql:uri"/>
                </xsl:attribute>
                <xsl:value-of select="sparql:uri"/>
              </a></td>
            </xsl:when>
            <xsl:when test="sparql:literal">
              <xsl:if test="xml:lang">
                 <img>
                   <xsl:attribute name="src">
                     <xsl:value-of select="concat('/img/flags/',@xml:lang,'.gif')"/>
                   </xsl:attribute>
                 </img>
              </xsl:if>
              <td>&#x201c;<xsl:value-of select="sparql:literal"/>&#x201d;</td>
            </xsl:when>
            <xsl:when test="sparql:bnode">
              <em>Blank Node <xsl:value-of select="sparql:bnode"/></em>
            </xsl:when>
          </xsl:choose>
        </xsl:for-each>
      </tr>
    </xsl:for-each>
  </table>
</xsl:template>

</xsl:stylesheet>
